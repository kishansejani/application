<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CustomerAccounts;
use App\Services\Otp\OtpException;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Storefront customer accounts: password-less login and sign-up with an SMS one-time code.
 *
 * Session keys
 *   otp_flow              ['phone' => ..., 'purpose' => login|register]
 *   pending_registration  ['name', 'phone', 'email', 'language'] until the code is verified
 */
class CustomerAuthController extends Controller
{
    private const PHONE_RULE = ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'];

    public function __construct(private OtpService $otp, private CustomerAccounts $accounts)
    {
    }

    // ================================================================= login

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('frontend.auth.login', [
            'phone' => old('phone', $this->cleanPhone($request->query('phone'))),
            'demoCode' => $this->otp->demoCode(),
            'otpLength' => $this->otp->length(),
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);
        $v = Validator::make($request->all(), ['phone' => self::PHONE_RULE], ['phone.*' => __('auth_ui.phone_invalid')]);
        if ($v->fails()) {
            return $this->failed($request, $v->errors()->first('phone'), 'login', 422, $v->errors()->toArray());
        }
        $phone = $request->input('phone');

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            // No silent auto-create any more: new numbers go through sign-up.
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'needs_registration' => true,
                    'message' => __('auth_ui.needs_registration'),
                    'redirect' => route('register', ['phone' => $phone]),
                ], 404);
            }
            return redirect()->route('register', ['phone' => $phone])->with('auth_notice', __('auth_ui.login_unregistered'));
        }
        if ($user->is_active === false) {
            return $this->failed($request, __('auth_ui.account_inactive'), 'login', 403);
        }

        return $this->dispatchCode($request, $phone, OtpService::LOGIN, 'login');
    }

    // ================================================================= register

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $pending = Session::get('pending_registration', []);

        return view('frontend.auth.register', [
            'phone' => old('phone', $this->cleanPhone($request->query('phone')) ?: ($pending['phone'] ?? '')),
            'pending' => $pending,
            'otpLength' => $this->otp->length(),
        ]);
    }

    public function register(Request $request)
    {
        $request->merge([
            'phone' => $this->cleanPhone($request->input('phone')),
            'email' => $request->filled('email') ? mb_strtolower(trim($request->input('email'))) : null,
            'name' => trim((string) $request->input('name')),
        ]);

        $v = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'phone' => self::PHONE_RULE,
            'email' => 'nullable|email:rfc|max:150|unique:users,email',
            'language' => 'required|in:en,gu',
            'terms' => 'accepted',
        ], [
            'phone.*' => __('auth_ui.phone_invalid'),
            'email.unique' => __('auth_ui.email_taken'),
            'terms.accepted' => __('auth_ui.terms_required'),
        ]);

        $v->after(function ($validator) use ($request) {
            if (!$validator->errors()->has('phone') && User::where('phone', $request->input('phone'))->exists()) {
                $validator->errors()->add('phone', __('auth_ui.phone_registered'));
                Session::flash('phone_registered', $request->input('phone'));
            }
        });

        if ($v->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $v->errors()->first(),
                    'errors' => $v->errors(),
                    'already_registered' => Session::has('phone_registered'),
                ], 422);
            }
            return redirect()->route('register')->withErrors($v, 'register')->withInput();
        }

        Session::put('pending_registration', [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'language' => $request->input('language'),
        ]);

        return $this->dispatchCode($request, $request->input('phone'), OtpService::REGISTER, 'register');
    }

    // ================================================================= verify

    public function showVerify()
    {
        $flow = Session::get('otp_flow');
        if (!$flow || empty($flow['phone'])) {
            return redirect()->route('customer.login')->with('warning', __('auth_ui.enter_phone_first'));
        }

        return view('frontend.auth.verify', [
            'phone' => $flow['phone'],
            'purpose' => $flow['purpose'],
            'otpLength' => $this->otp->length(),
            'resendIn' => $this->otp->resendIn($flow['phone'], $flow['purpose']),
            'resendSeconds' => $this->otp->resendSeconds(),
            'expiryMinutes' => $this->otp->expiryMinutes(),
            'demoCode' => $this->otp->demoCode(),
            'changeUrl' => $flow['purpose'] === OtpService::REGISTER
                ? route('register')
                : route('login', ['phone' => $flow['phone']]),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $flow = Session::get('otp_flow');
        if (!$flow || empty($flow['phone'])) {
            return $this->failed($request, __('auth_ui.session_expired'), 'login', 419, [], route('login'));
        }
        $phone = $flow['phone'];
        $purpose = $flow['purpose'];
        $len = $this->otp->length();

        $otp = preg_replace('/\D/', '', (string) $request->input('otp'));
        if (strlen($otp) !== $len) {
            return $this->otpFailed($request, __('auth_ui.otp_invalid_length', ['length' => $len]));
        }

        try {
            $this->otp->verify($phone, $purpose, $otp);
        } catch (OtpException $e) {
            return $this->otpFailed($request, $e->getMessage(), $e);
        }

        if ($purpose === OtpService::REGISTER) {
            $pending = Session::get('pending_registration');
            if (!$pending || ($pending['phone'] ?? null) !== $phone) {
                return $this->failed($request, __('auth_ui.pending_missing'), 'register', 419, [], route('register'));
            }
            $user = $this->accounts->register($pending);
        } else {
            $user = User::where('phone', $phone)->first();
            if (!$user) {
                return $this->failed($request, __('auth_ui.needs_registration'), 'login', 404, [], route('register', ['phone' => $phone]));
            }
            if ($user->is_active === false) {
                return $this->failed($request, __('auth_ui.account_inactive'), 'login', 403, [], route('login'));
            }
        }

        // Grab the guest session id before it is regenerated, then hand its cart over.
        $guestSession = Session::getId();
        $this->accounts->claimGuestCart($guestSession, $user);

        Auth::login($user, true);
        $request->session()->regenerate();
        Session::forget(['otp_flow', 'otp_phone', 'pending_registration']);

        if (in_array($user->language, ['en', 'gu'], true)) {
            Session::put('locale', $user->language);
            app()->setLocale($user->language); // greet in the user's language
        }
        $message = $purpose === OtpService::REGISTER
            ? __('auth_ui.welcome_new', ['name' => $user->name])
            : __('auth_ui.welcome_back', ['name' => $user->name ?: __('messages.store_name')]);

        $target = redirect()->intended(route('home'))->getTargetUrl();
        Session::flash('success', $message);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'redirect' => $target]);
        }
        return redirect()->to($target);
    }

    public function resendOtp(Request $request)
    {
        $flow = Session::get('otp_flow');
        if (!$flow || empty($flow['phone'])) {
            return $this->failed($request, __('auth_ui.session_expired'), 'login', 419, [], route('login'));
        }

        return $this->dispatchCode($request, $flow['phone'], $flow['purpose'], $flow['purpose'], true);
    }

    // ================================================================= profile

    public function profile()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login');
        }
        $user = Auth::user();
        return view('frontend.auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login');
        }

        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'language' => 'required|in:en,gu',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'language' => $request->language,
        ]);

        Session::put('locale', $request->language);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }

    // ================================================================= helpers

    /** Send (or resend) a code and move to the verify screen. */
    private function dispatchCode(Request $request, string $phone, string $purpose, string $bag, bool $resend = false)
    {
        try {
            $info = $this->otp->send($phone, $purpose);
        } catch (OtpException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()] + $e->toArray(), $e->status());
            }
            if ($resend) {
                return redirect()->route('customer.verify.view')->with('otp_error', $e->getMessage());
            }
            // Submitted the form again within the cool-down: the code sent a moment ago is still valid.
            if ($e->reason === OtpException::COOLDOWN && $this->otp->hasActiveCode($phone, $purpose)) {
                Session::put('otp_flow', ['phone' => $phone, 'purpose' => $purpose]);
                Session::put('otp_phone', $phone);
                return redirect()->route('customer.verify.view')->with('otp_notice', __('auth_ui.otp_sent_sms', ['phone' => $phone, 'length' => $this->otp->length()]));
            }
            return redirect()->route($bag === 'register' ? 'register' : 'login')
                ->withErrors(['phone' => $e->getMessage()], $bag)->withInput();
        }

        Session::put('otp_flow', ['phone' => $phone, 'purpose' => $purpose]);
        Session::put('otp_phone', $phone); // legacy key

        $message = $resend ? __('auth_ui.otp_resent') : __('auth_ui.otp_sent_sms', ['phone' => $phone, 'length' => $info['length']]);

        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'success' => true,
                'message' => $message,
                'phone' => $phone,
                'purpose' => $purpose,
                'resend_in' => $info['resend_in'],
                'expires_in' => $info['expires_in'],
                'redirect' => route('customer.verify.view'),
                'demo_otp' => $info['demo_code'], // null (dropped) unless demo mode
            ], fn ($v) => $v !== null));
        }

        return redirect()->route('customer.verify.view')->with('otp_notice', $message);
    }

    private function otpFailed(Request $request, string $message, ?OtpException $e = null)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'code_gone' => $e?->codeIsGone() ?? false] + ($e?->toArray() ?? []), $e?->status() ?? 422);
        }
        return redirect()->route('customer.verify.view')->with('otp_error', $message);
    }

    private function failed(Request $request, string $message, string $bag, int $status = 422, array $errors = [], ?string $redirect = null)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter(['success' => false, 'message' => $message, 'errors' => $errors ?: null, 'redirect' => $redirect]), $status);
        }
        if ($redirect) {
            return redirect()->to($redirect)->with('error', $message);
        }
        return back()->withErrors(['phone' => $message], $bag)->withInput();
    }

    private function cleanPhone($phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        return $digits;
    }
}
