<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\OfferController;

/*
|--------------------------------------------------------------------------
| Mobile Flutter App REST API Routes
|--------------------------------------------------------------------------
*/

// Public Endpoints
Route::post('/auth/otp/send', [AuthController::class, 'sendOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/auth/forgot-password/send-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'apiSendOtp']);
Route::post('/auth/forgot-password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'apiResetPassword']);

Route::get('/home', [HomeController::class, 'index']);
Route::get('/settings', function() {
    return response()->json([
        'success' => true,
        'settings' => \App\Models\Setting::getAllSettings(),
    ]);
});
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::get('/delivery-slot', [CheckoutController::class, 'getDeliverySlot']);

// Authenticated Endpoints (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // User Profile
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/{productId}', [CartController::class, 'remove']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // Checkout & Orders
    Route::post('/checkout/coupon/apply', [CheckoutController::class, 'applyCoupon']);
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
});
