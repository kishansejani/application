<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
        ]);

        $phone = $request->phone;
        $otp = '1234'; // Demo fixed OTP or rand(1000, 9999)
        $expiresAt = Carbon::now()->addMinutes(10);

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'User ' . substr($phone, -4),
                'phone' => $phone,
                'role' => 'customer',
                'language' => $request->header('Accept-Language', 'en') === 'gu' ? 'gu' : 'en',
                'otp' => $otp,
                'otp_expires_at' => $expiresAt,
            ]);
        } else {
            $user->otp = $otp;
            $user->otp_expires_at = $expiresAt;
            $user->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully to mobile.',
            'data' => [
                'phone' => $phone,
                'demo_otp' => $otp,
                'expires_in_minutes' => 10,
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'otp' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }

        if ($request->otp !== '1234' && ($user->otp !== $request->otp || Carbon::now()->gt($user->otp_expires_at))) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Logged in successfully.',
            'data' => [
                'token' => $token,
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
