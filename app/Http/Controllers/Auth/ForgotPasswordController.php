<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use App\Services\Otp\OtpException;
use App\Services\OtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Password reset with a one-time code sent by e-mail (plus a signed one-click link) or SMS.
 *
 * The answer to "send me a code" is always the same whether or not the account exists,
 * so the form cannot be used to discover registered e-mails / numbers.
 *
 * Session keys: reset_target, reset_channel, reset_user_id, reset_verified_at
 */
class ForgotPasswordController extends Controller
{
    /** Minutes the "verified" state lasts before the new password must be set. */
    private const VERIFIED_TTL = 15;

    public function __construct(private OtpService $otp)
    {
    }

    // ================================================================= web

    public function showForgotPassword()
    {
        return view('auth.forgot-password', ['otpLength' => $this->otp->length()]);
    }

    public function sendOtp(Request $request)
    {
        [$target, $channel] = $this->parseTarget($request);
        if (!$target) {
            return back()->withErrors(['email_or_phone' => __('auth_ui.invalid_identifier')])->withInput();
        }

        try {
            $this->issue($target, $channel);
        } catch (OtpException $e) {
            // Re-submitted within the cool-down: the code sent a moment ago is still valid.
            if (!($e->reason === OtpException::COOLDOWN && $this->otp->hasActiveCode($target, OtpService::RESET))) {
                return back()->withErrors(['email_or_phone' => $e->getMessage()])->withInput();
            }
        }

        Session::put(['reset_target' => $target, 'reset_channel' => $channel]);
        Session::forget(['reset_user_id', 'reset_verified_at', 'otp_verified']);

        return redirect()->route('password.verify.show')
            ->with('success', __('auth_ui.reset_generic', ['target' => $this->mask($target, $channel)]));
    }

    public function showVerifyOtp()
    {
        $target = Session::get('reset_target');
        if (!$target) {
            return redirect()->route('password.forgot')->with('error', __('auth_ui.session_expired'));
        }
        $channel = Session::get('reset_channel', str_contains($target, '@') ? 'email' : 'sms');

        return view('auth.verify-reset-otp', [
            'target' => $target,
            'maskedTarget' => $this->mask($target, $channel),
            'channel' => $channel,
            'otpLength' => $this->otp->length(),
            'resendIn' => $this->otp->resendIn($target, OtpService::RESET),
            'resendSeconds' => $this->otp->resendSeconds(),
            'expiryMinutes' => $this->otp->expiryMinutes(),
            'demoCode' => $this->otp->demoCode($channel),
        ]);
    }

    public function resendOtp(Request $request)
    {
        $target = Session::get('reset_target');
        if (!$target) {
            return $this->webFail($request, __('auth_ui.session_expired'), route('password.forgot'), 419);
        }
        $channel = Session::get('reset_channel', 'email');

        try {
            $info = $this->issue($target, $channel);
        } catch (OtpException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()] + $e->toArray(), $e->status());
            }
            return redirect()->route('password.verify.show')->with('otp_error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('auth_ui.otp_resent'), 'resend_in' => $info['resend_in']]);
        }
        return redirect()->route('password.verify.show')->with('success', __('auth_ui.otp_resent'));
    }

    public function verifyOtp(Request $request)
    {
        $target = Session::get('reset_target');
        if (!$target) {
            return $this->webFail($request, __('auth_ui.session_expired'), route('password.forgot'), 419);
        }

        $len = $this->otp->length();
        $otp = preg_replace('/\D/', '', (string) $request->input('otp'));
        if (strlen($otp) !== $len) {
            return $this->otpFail($request, __('auth_ui.otp_invalid_length', ['length' => $len]));
        }

        try {
            $this->otp->verify($target, OtpService::RESET, $otp);
        } catch (OtpException $e) {
            return $this->otpFail($request, $e->getMessage(), $e);
        }

        $user = $this->findUser($target);
        if (!$user) { // cannot normally happen: unknown targets get an undeliverable code
            return $this->otpFail($request, __('auth_ui.otp_not_found'));
        }

        Session::put(['reset_user_id' => $user->id, 'reset_verified_at' => now()->timestamp]);
        Session::flash('success', __('auth_ui.reset_verified'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('auth_ui.reset_verified'), 'redirect' => route('password.reset.show')]);
        }
        return redirect()->route('password.reset.show');
    }

    /** GET /reset-password/{token}?email=… (signed link from the e-mail). */
    public function resetFromLink(Request $request, string $token)
    {
        $user = User::where('email', mb_strtolower((string) $request->query('email')))->first();
        if (!$user || !Password::broker()->tokenExists($user, $token)) {
            return redirect()->route('password.forgot')->with('error', __('auth_ui.reset_link_invalid'));
        }

        Session::put([
            'reset_target' => $user->email,
            'reset_channel' => 'email',
            'reset_user_id' => $user->id,
            'reset_verified_at' => now()->timestamp,
        ]);

        return redirect()->route('password.reset.show');
    }

    public function showResetPassword()
    {
        if (!$this->resetUser()) {
            return redirect()->route('password.forgot')->with('error', __('auth_ui.verify_first'));
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $user = $this->resetUser();
        if (!$user) {
            return redirect()->route('password.forgot')->with('error', __('auth_ui.verify_first'));
        }

        $request->validate([
            'password' => 'required|string|min:8|max:100|confirmed',
        ]);

        $this->applyNewPassword($user, $request->input('password'));
        Session::forget(['reset_target', 'reset_channel', 'reset_user_id', 'reset_verified_at', 'otp_verified']);

        return redirect()->route($user->isAdmin() ? 'admin.login' : 'login')->with('success', __('auth_ui.reset_done'));
    }

    // ================================================================= API

    /** POST /api/auth/forgot-password/send-otp {email_or_phone} */
    public function apiSendOtp(Request $request)
    {
        [$target, $channel] = $this->parseTarget($request);
        if (!$target) {
            return response()->json([
                'status' => false, 'success' => false,
                'message' => __('auth_ui.invalid_identifier'),
                'errors' => ['email_or_phone' => [__('auth_ui.invalid_identifier')]],
            ], 422);
        }

        try {
            $info = $this->issue($target, $channel);
        } catch (OtpException $e) {
            if (!($e->reason === OtpException::COOLDOWN && $this->otp->hasActiveCode($target, OtpService::RESET))) {
                return response()->json(['status' => false, 'success' => false, 'message' => $e->getMessage()] + $e->toArray(), $e->status());
            }
            $info = [
                'length' => $this->otp->length(),
                'expires_in' => $this->otp->expiryMinutes() * 60,
                'resend_in' => $e->retryAfter,
                'demo_code' => $this->otp->demoCode($channel),
            ];
        }

        $data = [
            'channel' => $channel,
            'otp_length' => $info['length'],
            'expires_in_minutes' => intdiv($info['expires_in'], 60),
            'resend_in' => $info['resend_in'],
        ];
        $payload = [
            'status' => true,
            'success' => true,
            'message' => __('auth_ui.reset_generic', ['target' => $this->mask($target, $channel)]),
            'data' => $data,
        ];
        if ($info['demo_code'] !== null) {
            $payload['data']['demo_otp'] = $info['demo_code'];
            $payload['demo_otp'] = $info['demo_code']; // legacy key, demo mode only
        }

        return response()->json($payload);
    }

    /** POST /api/auth/forgot-password/reset {email_or_phone, otp, password, password_confirmation?} */
    public function apiResetPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string|max:150',
            'otp' => 'required|string|max:10',
            'password' => 'required|string|min:8|max:100' . ($request->has('password_confirmation') ? '|confirmed' : ''),
        ]);

        [$target] = $this->parseTarget($request);
        if (!$target) {
            return response()->json(['status' => false, 'success' => false, 'message' => __('auth_ui.invalid_identifier')], 422);
        }

        try {
            $this->otp->verify($target, OtpService::RESET, (string) $request->input('otp'));
        } catch (OtpException $e) {
            return response()->json(['status' => false, 'success' => false, 'message' => $e->getMessage()] + $e->toArray(), $e->status());
        }

        $user = $this->findUser($target);
        if (!$user) {
            return response()->json(['status' => false, 'success' => false, 'message' => __('auth_ui.otp_not_found')], 422);
        }

        $this->applyNewPassword($user, $request->input('password'));

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => __('auth_ui.reset_done'),
        ]);
    }

    // ================================================================= internals

    /**
     * Send a reset code to the target (or pretend to, when there is no such account).
     *
     * @throws OtpException
     */
    private function issue(string $target, string $channel): array
    {
        $user = $this->findUser($target);
        if (!$user) {
            return $this->otp->pretendSend($target, OtpService::RESET, $channel);
        }

        if ($channel === 'sms') {
            return $this->otp->send($target, OtpService::RESET, 'sms');
        }

        return $this->otp->send($target, OtpService::RESET, 'email', function (string $code, int $minutes) use ($user) {
            $linkMinutes = (int) config('auth.passwords.users.expire', 60);
            $token = Password::broker()->createToken($user);
            $url = URL::temporarySignedRoute('password.reset', now()->addMinutes($linkMinutes), [
                'token' => $token,
                'email' => $user->email,
            ]);

            Mail::to($user->email, $user->name)
                ->locale($user->language === 'gu' ? 'gu' : app()->getLocale())
                ->send(new PasswordResetCodeMail($user, $code, $minutes, $url, $linkMinutes));
        });
    }

    /** @return array{0: ?string, 1: string} [normalised target, channel] */
    private function parseTarget(Request $request): array
    {
        $raw = trim((string) $request->input('email_or_phone', $request->input('email', $request->input('phone', ''))));

        if (str_contains($raw, '@')) {
            $email = mb_strtolower($raw);
            return [filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 150 ? $email : null, 'email'];
        }

        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        return [preg_match('/^[0-9]{10}$/', $digits) ? $digits : null, 'sms'];
    }

    private function findUser(string $target): ?User
    {
        return str_contains($target, '@')
            ? User::whereRaw('LOWER(email) = ?', [mb_strtolower($target)])->first()
            : User::where('phone', $target)->first();
    }

    private function resetUser(): ?User
    {
        $id = Session::get('reset_user_id');
        $at = (int) Session::get('reset_verified_at');
        if (!$id || !$at || now()->timestamp - $at > self::VERIFIED_TTL * 60) {
            return null;
        }
        return User::find($id);
    }

    /** Set the password and sign the account out everywhere else. */
    private function applyNewPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => $password, // hashed by the model cast
            'remember_token' => Str::random(60), // kills "remember me" cookies on other devices
        ])->save();

        $user->tokens()->delete(); // mobile app / API sessions

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        if ($user->email) {
            Password::broker()->deleteToken($user); // one-click link becomes unusable
        }
        $this->otp->forget($user->email ?? '', OtpService::RESET);
        $this->otp->forget($user->phone ?? '', OtpService::RESET);

        event(new PasswordReset($user));
    }

    private function mask(string $target, string $channel): string
    {
        if ($channel === 'email') {
            [$local, $domain] = explode('@', $target, 2);
            $keep = mb_substr($local, 0, min(2, max(1, mb_strlen($local) - 1)));
            return $keep . str_repeat('•', max(2, mb_strlen($local) - mb_strlen($keep))) . '@' . $domain;
        }
        return '+91 ' . substr($target, 0, 2) . '•••••' . substr($target, -3);
    }

    private function otpFail(Request $request, string $message, ?OtpException $e = null)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'code_gone' => $e?->codeIsGone() ?? false] + ($e?->toArray() ?? []), $e?->status() ?? 422);
        }
        return redirect()->route('password.verify.show')->with('otp_error', $message);
    }

    private function webFail(Request $request, string $message, string $redirect, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'redirect' => $redirect], $status);
        }
        return redirect()->to($redirect)->with('error', $message);
    }
}
