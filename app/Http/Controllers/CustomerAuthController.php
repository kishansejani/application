<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CartItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('frontend.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $phone = $request->phone;
        // Generate 4-digit OTP (for demo simplicity: 1234 or random 4 digits)
        $otp = '1234'; // Standard predictable test OTP or rand(1000, 9999)
        $expiresAt = Carbon::now()->addMinutes(10);

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Customer ' . substr($phone, -4),
                'phone' => $phone,
                'role' => 'customer',
                'language' => session('locale', 'en'),
                'otp' => $otp,
                'otp_expires_at' => $expiresAt,
            ]);
        } else {
            $user->otp = $otp;
            $user->otp_expires_at = $expiresAt;
            $user->save();
        }

        Session::put('otp_phone', $phone);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully! (Demo OTP: ' . $otp . ')',
                'otp' => $otp,
                'phone' => $phone,
            ]);
        }

        return redirect()->route('customer.verify.view')->with('success', 'OTP sent successfully to +91 ' . $phone . ' (Use Demo OTP: ' . $otp . ')');
    }

    public function showVerify()
    {
        $phone = Session::get('otp_phone');
        if (!$phone) {
            return redirect()->route('customer.login')->with('warning', 'Please enter your mobile number first.');
        }
        return view('frontend.auth.verify', compact('phone'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'otp' => 'required|string|min:4|max:6',
        ]);

        $phone = $request->phone;
        $otp = trim($request->otp);

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withInput()->with('error', 'User not found for this mobile number.');
        }

        // Check OTP (accept either stored OTP or standard demo master 1234)
        if ($otp !== '1234' && ($user->otp !== $otp || Carbon::now()->gt($user->otp_expires_at))) {
            return back()->withInput()->with('error', 'Invalid or expired OTP. Please try again.');
        }

        // Login user
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        Auth::login($user, true);

        // Migrate guest cart items to user
        $sessionId = Session::getId();
        CartItem::where('session_id', $sessionId)->update([
            'user_id' => $user->id,
            'session_id' => null,
        ]);

        // Sync locale
        if ($user->language) {
            Session::put('locale', $user->language);
        }

        return redirect()->intended(route('home'))->with('success', 'Welcome, ' . ($user->name ?: 'valued customer') . '!');
    }

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
}
