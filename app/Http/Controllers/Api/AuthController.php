<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerAccounts;
use App\Services\Otp\OtpException;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    private const PHONE_RULE = ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'];

    public function __construct(private OtpService $otp, private CustomerAccounts $accounts)
    {
    }

    /**
     * POST /api/auth/otp/send {phone}
     * Login code for a registered number. Unregistered -> 404 {needs_registration: true}.
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => self::PHONE_RULE], ['phone.*' => __('auth_ui.phone_invalid')]);
        $phone = $request->input('phone');

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'success' => false,
                'needs_registration' => true,
                'message' => __('auth_ui.needs_registration'),
            ], 404);
        }
        if ($user->is_active === false) {
            return response()->json(['status' => false, 'success' => false, 'message' => __('auth_ui.account_inactive')], 403);
        }

        return $this->sendCode($phone, OtpService::LOGIN);
    }

    /**
     * POST /api/auth/register {name, phone, email?, language?}
     * Stores the details for otp_expiry minutes and texts a code. Finish with /auth/otp/verify.
     */
    public function register(Request $request)
    {
        $request->merge(['email' => $request->filled('email') ? mb_strtolower(trim($request->input('email'))) : null]);
        $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone' => self::PHONE_RULE,
            'email' => 'nullable|email:rfc|max:150|unique:users,email',
            'language' => 'nullable|in:en,gu',
        ], [
            'phone.*' => __('auth_ui.phone_invalid'),
            'email.unique' => __('auth_ui.email_taken'),
        ]);
        $phone = $request->input('phone');

        if (User::where('phone', $phone)->exists()) {
            return response()->json([
                'status' => false,
                'success' => false,
                'already_registered' => true,
                'message' => __('auth_ui.phone_registered'),
                'errors' => ['phone' => [__('auth_ui.phone_registered')]],
            ], 422);
        }

        $language = $request->input('language') ?: (app()->getLocale() === 'gu' ? 'gu' : 'en');
        $pending = [
            'name' => trim($request->input('name')),
            'phone' => $phone,
            'email' => $request->input('email'),
            'language' => $language,
        ];

        $response = $this->sendCode($phone, OtpService::REGISTER);
        if (in_array($response->getStatusCode(), [200, 429], true)) { // 429: earlier code still valid
            Cache::put($this->pendingKey($phone), $pending, now()->addMinutes($this->otp->expiryMinutes() + 10));
        }
        return $response;
    }

    /**
     * POST /api/auth/otp/resend {phone, purpose: login|register|reset}
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => self::PHONE_RULE,
            'purpose' => 'nullable|in:login,register,reset',
        ], ['phone.*' => __('auth_ui.phone_invalid')]);
        $phone = $request->input('phone');
        $purpose = $request->input('purpose') ?: (User::where('phone', $phone)->exists() ? OtpService::LOGIN : OtpService::REGISTER);

        $exists = User::where('phone', $phone)->exists();
        if ($purpose === OtpService::LOGIN && !$exists) {
            return response()->json(['status' => false, 'success' => false, 'needs_registration' => true, 'message' => __('auth_ui.needs_registration')], 404);
        }
        if ($purpose === OtpService::REGISTER) {
            if ($exists) {
                return response()->json(['status' => false, 'success' => false, 'already_registered' => true, 'message' => __('auth_ui.phone_registered')], 422);
            }
            if (!Cache::has($this->pendingKey($phone))) {
                return response()->json(['status' => false, 'success' => false, 'message' => __('auth_ui.pending_missing')], 410);
            }
            Cache::put($this->pendingKey($phone), Cache::get($this->pendingKey($phone)), now()->addMinutes($this->otp->expiryMinutes() + 10));
        }
        if ($purpose === OtpService::RESET && !$exists) {
            // Same answer as a real send - do not reveal whether the account exists.
            try {
                $info = $this->otp->pretendSend($phone, OtpService::RESET);
            } catch (OtpException $e) {
                return $this->otpError($e);
            }
            return $this->sentResponse($phone, OtpService::RESET, $info);
        }

        return $this->sendCode($phone, $purpose, true);
    }

    /**
     * POST /api/auth/otp/verify {phone, otp, purpose?}
     * Logs in, or - when a sign-up is pending for the phone - creates the account. Returns a Sanctum token.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => self::PHONE_RULE,
            'otp' => 'required|string|max:10',
            'purpose' => 'nullable|in:login,register',
        ], ['phone.*' => __('auth_ui.phone_invalid')]);
        $phone = $request->input('phone');

        $user = User::where('phone', $phone)->first();
        $pending = Cache::get($this->pendingKey($phone));
        $purpose = $request->input('purpose') ?: ($user ? OtpService::LOGIN : OtpService::REGISTER);

        if ($purpose === OtpService::REGISTER && !$user && !$pending) {
            return response()->json([
                'status' => false,
                'success' => false,
                'needs_registration' => true,
                'message' => __('auth_ui.needs_registration'),
            ], 404);
        }
        // A pending sign-up whose number got registered elsewhere meanwhile: treat as login.
        if ($purpose === OtpService::REGISTER && $user) {
            $purpose = OtpService::LOGIN;
        }

        try {
            $this->otp->verify($phone, $purpose, (string) $request->input('otp'));
        } catch (OtpException $e) {
            // A register code is also accepted when the client asked for "login" by mistake.
            if ($purpose === OtpService::LOGIN && $pending && $e->reason === OtpException::NOT_FOUND) {
                try {
                    $this->otp->verify($phone, OtpService::REGISTER, (string) $request->input('otp'));
                    $purpose = OtpService::REGISTER;
                } catch (OtpException $e2) {
                    return $this->otpError($e2);
                }
            } else {
                return $this->otpError($e);
            }
        }

        $created = false;
        if (!$user) {
            $user = $this->accounts->register($pending);
            $created = true;
        }
        Cache::forget($this->pendingKey($phone));

        if ($user->is_active === false) {
            return response()->json(['status' => false, 'success' => false, 'message' => __('auth_ui.account_inactive')], 403);
        }

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => $created ? __('auth_ui.registered') : __('auth_ui.logged_in'),
            'data' => [
                'token' => $token,
                'is_new_user' => $created,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'language' => $user->language,
                    'role' => $user->role,
                ],
            ],
        ]);
    }

    // ---------------------------------------------------------------- helpers

    private function sendCode(string $phone, string $purpose, bool $isResend = false)
    {
        try {
            $info = $this->otp->send($phone, $purpose);
        } catch (OtpException $e) {
            // Asked again within the cool-down while the previous code is still valid: let the
            // client continue to the code screen instead of failing (explicit resends still get 429).
            if (!$isResend && $e->reason === OtpException::COOLDOWN && $this->otp->hasActiveCode($phone, $purpose)) {
                return $this->sentResponse($phone, $purpose, [
                    'length' => $this->otp->length(),
                    'expires_in' => $this->otp->expiryMinutes() * 60,
                    'resend_in' => $e->retryAfter,
                    'demo_code' => $this->otp->demoCode(),
                ]);
            }
            return $this->otpError($e);
        }
        return $this->sentResponse($phone, $purpose, $info);
    }

    private function sentResponse(string $phone, string $purpose, array $info)
    {
        $data = [
            'phone' => $phone,
            'purpose' => $purpose,
            'otp_length' => $info['length'],
            'expires_in_minutes' => intdiv($info['expires_in'], 60),
            'resend_in' => $info['resend_in'],
        ];
        if ($info['demo_code'] !== null) {
            $data['demo_otp'] = $info['demo_code']; // demo mode only - never with a real SMS gateway
        }

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => __('auth_ui.otp_sent_sms', ['phone' => $phone, 'length' => $info['length']]),
            'data' => $data,
        ]);
    }

    private function otpError(OtpException $e)
    {
        return response()->json([
            'status' => false,
            'success' => false,
            'message' => $e->getMessage(),
        ] + $e->toArray(), $e->status());
    }

    private function pendingKey(string $phone): string
    {
        return 'otp:pending_registration:' . $phone;
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'language' => $user->language,
                'saved_addresses_count' => $user->addresses()->count(),
                'orders_count' => $user->orders()->count(),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'language' => 'nullable|in:en,gu',
        ]);

        $user->update($request->only(['name', 'email', 'language']));

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
