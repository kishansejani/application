<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\PageViewController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Auth\ForgotPasswordController;

use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\SliderController as AdminSlider;
use App\Http\Controllers\Admin\CategoryController as AdminCategory;
use App\Http\Controllers\Admin\SubCategoryController as AdminSubCategory;
use App\Http\Controllers\Admin\ProductController as AdminProduct;
use App\Http\Controllers\Admin\StockController as AdminStock;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoice;
use App\Http\Controllers\Admin\OfferController as AdminOffer;
use App\Http\Controllers\Admin\PageController as AdminPage;
use App\Http\Controllers\Admin\SettingController as AdminSetting;
use App\Http\Controllers\Admin\RoleController as AdminRole;
use App\Http\Controllers\Admin\UserController as AdminUser;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Language Switcher
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Customer Storefront Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Products & Categories
Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
Route::get('/product/{product:slug}', [ProductCatalogController::class, 'show'])->name('products.show');
Route::get('/product/quick-view/{product}', [ProductCatalogController::class, 'quickView'])->name('products.quick_view');
Route::get('/categories', [ProductCatalogController::class, 'categories'])->name('categories.index');
Route::get('/category/{category:slug}', [ProductCatalogController::class, 'categoryDetails'])->name('categories.show');
Route::get('/subcategory/{subcategory:slug}', [ProductCatalogController::class, 'subCategoryDetails'])->name('subcategories.show');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/drawer', [CartController::class, 'getDrawerData'])->name('cart.drawer');

// Checkout & Delivery logic (2 Hours vs Next Day)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/coupon/apply', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
Route::post('/checkout/coupon/remove', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place_order');
Route::get('/order/confirmed/{orderNumber}', [CheckoutController::class, 'confirmed'])->name('order.confirmed');

// Orders & Tracking & Invoices
Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
Route::get('/order/track', [CustomerOrderController::class, 'track'])->name('order.track');
Route::get('/order/{orderNumber}', [CustomerOrderController::class, 'show'])->name('order.show');
Route::get('/order/invoice/{orderNumber}', [CustomerOrderController::class, 'invoice'])->name('order.invoice');

// Wishlist
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Addresses & Interactive Map Picker
Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.set_default');
Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

// Customer Auth (Mobile OTP)
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login/otp/send', [CustomerAuthController::class, 'sendOtp'])->name('auth.send-otp');
Route::get('/login/verify', [CustomerAuthController::class, 'showVerify'])->name('auth.verify.view');
Route::post('/login/verify', [CustomerAuthController::class, 'verifyOtp'])->name('auth.verify');
Route::get('/profile', [CustomerAuthController::class, 'profile'])->name('profile.index');
Route::put('/profile/update', [CustomerAuthController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/update', [CustomerAuthController::class, 'updateProfile']);
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Forgot Password Flow
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.otp.send');
Route::get('/forgot-password/verify-otp', [ForgotPasswordController::class, 'showVerifyOtp'])->name('password.verify.show');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify.post');
Route::get('/forgot-password/reset-password', [ForgotPasswordController::class, 'showResetPassword'])->name('password.reset.show');
Route::post('/forgot-password/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.post');

// Pages & Offers
Route::get('/offers', [PageViewController::class, 'offers'])->name('pages.offers');
Route::get('/page/{slug}', [PageViewController::class, 'show'])->name('pages.show');

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuth::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuth::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuth::class, 'logout'])->name('logout');

    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Manage Sliders
        Route::resource('sliders', AdminSlider::class);
        Route::patch('sliders/{slider}/toggle-status', [AdminSlider::class, 'toggleStatus'])->name('sliders.toggle_status');

        // Manage Categories
        Route::resource('categories', AdminCategory::class);
        Route::patch('categories/{category}/toggle-status', [AdminCategory::class, 'toggleStatus'])->name('categories.toggle_status');

        // Manage Sub Categories
        Route::resource('subcategories', AdminSubCategory::class);
        Route::get('categories/{category}/subcategories', [AdminSubCategory::class, 'getByCategory'])->name('subcategories.by_category');

        // Manage Products
        Route::resource('products', AdminProduct::class);
        Route::delete('products/images/{image}', [AdminProduct::class, 'deleteImage'])->name('products.images.delete');
        Route::patch('products/{product}/toggle-featured', [AdminProduct::class, 'toggleFeatured'])->name('products.toggle_featured');
        Route::patch('products/{product}/toggle-status', [AdminProduct::class, 'toggleStatus'])->name('products.toggle_status');

        // Manage Stock
        Route::get('/stock', [AdminStock::class, 'index'])->name('stock.index');
        Route::patch('/stock/{product}', [AdminStock::class, 'update'])->name('stock.update');
        Route::post('/stock/bulk-adjust', [AdminStock::class, 'bulkAdjust'])->name('stock.bulk_adjust');

        // Manage Orders
        Route::get('/orders', [AdminOrder::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrder::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrder::class, 'updateStatus'])->name('orders.update_status');
        Route::patch('/orders/{order}/payment', [AdminOrder::class, 'updatePayment'])->name('orders.update_payment');

        // Invoices
        Route::get('/invoices/{order}', [AdminInvoice::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{order}/print', [AdminInvoice::class, 'print'])->name('invoices.print');

        // Offers / Promo Coupons
        Route::resource('offers', AdminOffer::class);
        Route::patch('offers/{offer}/toggle-status', [AdminOffer::class, 'toggleStatus'])->name('offers.toggle_status');

        // Manage Static & Legal Pages
        Route::get('/pages', [AdminPage::class, 'index'])->name('pages.index');
        Route::get('/pages/{page}/edit', [AdminPage::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [AdminPage::class, 'update'])->name('pages.update');

        // Roles & Permissions Management (ROLE & USER MANAGEMENT)
        Route::resource('roles', AdminRole::class);

        // Users Management
        Route::resource('users', AdminUser::class);
        Route::patch('users/{user}/toggle-status', [AdminUser::class, 'toggleStatus'])->name('users.toggle-status');

        // System Settings & Theme Customization (SYSTEM SETTINGS)
        Route::get('/settings', [AdminSetting::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSetting::class, 'update'])->name('settings.update');
        Route::get('/settings/reset', [AdminSetting::class, 'reset'])->name('settings.reset');
    });
});
