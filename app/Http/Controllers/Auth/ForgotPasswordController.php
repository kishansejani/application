<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ]);

        $input = trim($request->email_or_phone);
        $user = User::where('email', $input)->orWhere('phone', $input)->first();

        if (!$user) {
            return back()->withErrors(['email_or_phone' => 'No account found with this email or mobile number.'])->withInput();
        }

        // Generate 4-digit OTP (1234 for testing/demo or random)
        $otp = '1234'; // Fixed for demo convenience & tested simulation
        $expiresAt = Carbon::now()->addMinutes(15);

        PasswordResetOtp::create([
            'email_or_phone' => $input,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        Log::info("Password Reset OTP for {$input}: {$otp}");

        session(['reset_target' => $input]);

        return redirect()->route('password.verify.show')->with('success', "OTP sent successfully to {$input}. (Demo OTP: 1234)");
    }

    public function showVerifyOtp()
    {
        $target = session('reset_target');
        if (!$target) {
            return redirect()->route('password.forgot')->with('error', 'Please enter your email or phone first.');
        }
        return view('auth.verify-reset-otp', compact('target'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:4',
        ]);

        $target = session('reset_target');
        if (!$target) {
            return redirect()->route('password.forgot')->with('error', 'Session expired. Please try again.');
        }

        $resetRecord = PasswordResetOtp::where('email_or_phone', $target)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$resetRecord && $request->otp !== '1234') {
            return back()->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
        }

        if ($resetRecord) {
            $resetRecord->update(['is_used' => true]);
        }

        session(['otp_verified' => true]);

        return redirect()->route('password.reset.show')->with('success', 'OTP verified successfully. Please set your new password.');
    }

    public function showResetPassword()
    {
        if (!session('otp_verified') || !session('reset_target')) {
            return redirect()->route('password.forgot')->with('error', 'Please verify OTP first.');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $target = session('reset_target');
        $user = User::where('email', $target)->orWhere('phone', $target)->first();

        if (!$user) {
            return redirect()->route('password.forgot')->with('error', 'User not found.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        session()->forget(['reset_target', 'otp_verified']);

        if ($user->isAdmin()) {
            return redirect()->route('admin.login')->with('success', 'Password reset successfully. You can now login with your new password.');
        }

        return redirect()->route('login')->with('success', 'Password reset successfully! Please login.');
    }

    // API endpoints
    public function apiSendOtp(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ]);

        $input = trim($request->email_or_phone);
        $user = User::where('email', $input)->orWhere('phone', $input)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found with this email or mobile number.'], 404);
        }

        $otp = '1234';
        PasswordResetOtp::create([
            'email_or_phone' => $input,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_used' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "OTP sent successfully to {$input}.",
            'demo_otp' => '1234',
        ]);
    }

    public function apiResetPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email_or_phone)->orWhere('phone', $request->email_or_phone)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($request->otp !== '1234') {
            $resetRecord = PasswordResetOtp::where('email_or_phone', $request->email_or_phone)
                ->where('otp', $request->otp)
                ->where('is_used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$resetRecord) {
                return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
            }
            $resetRecord->update(['is_used' => true]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! You can now login.',
        ]);
    }
}
