<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CmsController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Middleware\AdminOnly;
use App\Http\Controllers\Api\DashboardStatsController;
use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ReviewLinkController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\ProviderMemberController;
use App\Http\Controllers\Api\ProviderVerificationController;
use App\Http\Controllers\Api\GuestBookingController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\GpsController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\PricingController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle.auth');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle.auth');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle.auth');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/auth/reset-password', [ResetPasswordController::class, 'reset']);
Route::post('/coupons/validate', [CouponController::class, 'validate']);
Route::get('/cars/{car}/availability', [AvailabilityController::class, 'check']);

// Public providers
Route::get('/providers', [ProviderController::class, 'index']);
Route::get('/providers/{provider}', [ProviderController::class, 'show']);
Route::get('/providers/{provider}/cars', [ProviderController::class, 'providerCars']);

// Public data
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{car}', [CarController::class, 'show']);
Route::get('/premium/plans', [PremiumController::class, 'plans']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);
Route::get('/cms', [CmsController::class, 'getCmsContent']);
Route::get('/cms/{key}', [CmsController::class, 'getCmsByKey']);
Route::post('/bookings/lookup', [BookingController::class, 'lookup'])->middleware('throttle:10,1');

// Guest Bookings (public)
Route::post('/guest-bookings', [GuestBookingController::class, 'store'])->middleware('throttle:10,1');
Route::get('/guest-bookings/{guestBooking}', [GuestBookingController::class, 'show']);
Route::post('/guest-bookings/lookup', [GuestBookingController::class, 'lookup'])->middleware('throttle:10,1');

// Reviews (public)
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/car/{carId}', [ReviewController::class, 'byCar']);
Route::post('/reviews/{review}/helpful', [ReviewController::class, 'helpful'])->middleware('throttle:10,1');

// Chat (public)
Route::post('/chats', [ChatController::class, 'store'])->middleware('throttle:20,1');

// Review links (public - no auth needed)
Route::get('/review-link/verify/{token}', [ReviewLinkController::class, 'verify']);
Route::post('/review-link/submit/{token}', [ReviewLinkController::class, 'submit'])->middleware('throttle:5,1');
Route::get('/chats/by-guest', [ChatController::class, 'byGuest']);
Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage'])->middleware('throttle:30,1');
Route::get('/chats/{chat}/messages', [ChatController::class, 'messages']);
Route::post('/chats/{chat}/read', [ChatController::class, 'markRead']);

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Profile
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::put('/profile/license', [UserController::class, 'updateLicense']);
    Route::put('/auth/password', [UserController::class, 'updatePassword']);

    // Email Verification
    Route::post('/email/verify', [EmailVerificationController::class, 'send'])->middleware('throttle:3,1');
    Route::get('/email/check', [EmailVerificationController::class, 'check']);

    // Dashboard Stats
    Route::get('/dashboard/stats', [DashboardStatsController::class, 'index']);

    // File uploads
    Route::post('/upload', [FileUploadController::class, 'upload'])->middleware('throttle:30,1');
    Route::post('/upload/multiple', [FileUploadController::class, 'uploadMultiple'])->middleware('throttle:10,1');
    Route::delete('/upload', [FileUploadController::class, 'destroy']);

    // Cars (driver/host)
    Route::post('/cars', [CarController::class, 'store']);
    Route::put('/cars/{car}', [CarController::class, 'update']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);
    Route::get('/cars/nearby', [CarController::class, 'nearby']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Payments (user)
    Route::post('/bookings/{booking}/payment', [PaymentController::class, 'initiate']);
    Route::get('/payments', [PaymentController::class, 'myPayments']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);

    // Reviews (authenticated)
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{carId}/toggle', [WishlistController::class, 'toggle']);
    Route::delete('/wishlist/{carId}', [WishlistController::class, 'destroy']);
    Route::get('/wishlist/{carId}/check', [WishlistController::class, 'check']);

    // Invoices
    Route::get('/invoices/{booking}/download', [InvoiceController::class, 'download']);
    Route::get('/invoices/{booking}/pdf', [InvoiceController::class, 'pdf']);

    // Premium
    Route::post('/premium/purchase', [PremiumController::class, 'purchase']);
    Route::get('/premium/my-cars', [PremiumController::class, 'myPremiumCars']);

    // Pricing
    Route::post('/pricing/calculate', [PricingController::class, 'calculate']);

    // GPS Tracking
    Route::post('/gps/update', [GpsController::class, 'updateLocation']);
    Route::get('/gps/car/{carId}', [GpsController::class, 'getCarLocation']);
    Route::get('/gps/booking/{bookingId}', [GpsController::class, 'getBookingLocation']);
    Route::get('/gps/track/{bookingId}', [GpsController::class, 'trackHistory']);

    // Loyalty & Referral
    Route::get('/loyalty/balance', [LoyaltyController::class, 'balance']);
    Route::get('/loyalty/history', [LoyaltyController::class, 'history']);
    Route::get('/loyalty/tier', [LoyaltyController::class, 'tier']);
    Route::post('/referral/generate', [LoyaltyController::class, 'generateReferral']);
    Route::get('/referral/stats', [LoyaltyController::class, 'referralStats']);
    Route::post('/referral/apply', [LoyaltyController::class, 'applyReferral']);

    // Provider Management
    Route::post('/providers', [ProviderController::class, 'store']);
    Route::get('/providers/mine', [ProviderController::class, 'mine']);
    Route::put('/providers/mine', [ProviderController::class, 'updateMine']);
    Route::get('/providers/mine/cars', [ProviderController::class, 'myCars']);
    Route::get('/providers/mine/stats', [ProviderController::class, 'myStats']);
    Route::post('/bookings/{booking}/assign-driver', [ProviderController::class, 'assignDriver']);

    // Provider Members (drivers)
    Route::get('/providers/mine/members', [ProviderMemberController::class, 'index']);
    Route::post('/providers/mine/members', [ProviderMemberController::class, 'store']);
    Route::put('/providers/mine/members/{member}', [ProviderMemberController::class, 'update']);
    Route::delete('/providers/mine/members/{member}', [ProviderMemberController::class, 'destroy']);
    Route::post('/providers/mine/members/{member}/license', [ProviderMemberController::class, 'submitLicense']);

    // Provider Verifications
    Route::get('/providers/mine/verifications', [ProviderVerificationController::class, 'index']);
    Route::post('/providers/mine/verifications', [ProviderVerificationController::class, 'store']);

    // CMS write (for admin)
    Route::middleware(AdminOnly::class)->prefix('admin')->group(function () {
        // Dashboard Stats
        Route::get('/stats', [AdminStatsController::class, 'index']);
        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        // All Bookings
        Route::get('/bookings', [BookingController::class, 'all']);
        Route::put('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);

        // Blog
        Route::get('/blog', [BlogController::class, 'adminIndex']);
        Route::post('/blog', [BlogController::class, 'store']);
        Route::put('/blog/{blogPost}', [BlogController::class, 'update']);
        Route::delete('/blog/{blogPost}', [BlogController::class, 'destroy']);

        // CMS
        Route::post('/cms', [CmsController::class, 'upsertCms']);

        // FAQ
        Route::get('/faq', [CmsController::class, 'faqIndex']);
        Route::post('/faq', [CmsController::class, 'faqStore']);
        Route::put('/faq/{faq}', [CmsController::class, 'faqUpdate']);
        Route::delete('/faq/{faq}', [CmsController::class, 'faqDestroy']);

        // Reviews
        Route::get('/reviews', [CmsController::class, 'reviewIndex']);
        Route::post('/reviews', [CmsController::class, 'reviewStore']);
        Route::put('/reviews/{review}', [CmsController::class, 'reviewUpdate']);
        Route::delete('/reviews/{review}', [CmsController::class, 'reviewDestroy']);

        // Offers
        Route::get('/offers', [CmsController::class, 'offerIndex']);
        Route::post('/offers', [CmsController::class, 'offerStore']);
        Route::put('/offers/{offer}', [CmsController::class, 'offerUpdate']);
        Route::delete('/offers/{offer}', [CmsController::class, 'offerDestroy']);

        // Timelines
        Route::get('/timelines', [CmsController::class, 'timelineIndex']);
        Route::post('/timelines', [CmsController::class, 'timelineStore']);
        Route::put('/timelines/{timeline}', [CmsController::class, 'timelineUpdate']);
        Route::delete('/timelines/{timeline}', [CmsController::class, 'timelineDestroy']);

        // Process Steps
        Route::get('/steps', [CmsController::class, 'stepIndex']);
        Route::post('/steps', [CmsController::class, 'stepStore']);
        Route::put('/steps/{step}', [CmsController::class, 'stepUpdate']);
        Route::delete('/steps/{step}', [CmsController::class, 'stepDestroy']);

        // Admin payments
        Route::get('/payments/pending', [PaymentController::class, 'pending']);
        Route::get('/payments/all', [PaymentController::class, 'all']);
        Route::put('/payments/{payment}/verify', [PaymentController::class, 'verify']);

        // Admin reviews
        Route::put('/reviews/{review}/respond', [ReviewController::class, 'respond']);
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

        // Admin loyalty
        Route::get('/loyalty/leaderboard', [LoyaltyController::class, 'leaderboard']);

        // Admin analytics (traffic, requests, visitors)
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('/analytics/booking-trends', [AnalyticsController::class, 'bookingTrends']);
        Route::get('/analytics/user-growth', [AnalyticsController::class, 'userGrowth']);
        Route::get('/analytics/car-utilization', [AnalyticsController::class, 'carUtilization']);

        // Chat
        Route::get('/chats', [ChatController::class, 'adminIndex']);
        Route::post('/chats/{chat}/reply', [ChatController::class, 'adminReply']);
        Route::post('/chats/{chat}/close', [ChatController::class, 'adminClose']);
        Route::get('/chats/unread', [ChatController::class, 'adminUnread']);

        // Review links
        Route::get('/review-links', [ReviewLinkController::class, 'index']);
        Route::post('/review-links', [ReviewLinkController::class, 'generate']);

        // Categories
        Route::get('/categories', [CategoryController::class, 'adminIndex']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Providers
        Route::get('/providers', [ProviderController::class, 'adminIndex']);
        Route::get('/providers/pending', [ProviderController::class, 'adminPending']);
        Route::get('/providers/{provider}', [ProviderController::class, 'adminShow']);
        Route::put('/providers/{provider}/verify', [ProviderController::class, 'adminVerify']);
        Route::put('/providers/{provider}/status', [ProviderController::class, 'adminToggleStatus']);
        Route::get('/providers/{provider}/members', [ProviderController::class, 'adminMembers']);
        Route::put('/providers/members/{member}/verify', [ProviderController::class, 'adminVerifyMember']);

        // Coupons
        Route::get('/coupons', [CouponController::class, 'index']);
        Route::post('/coupons', [CouponController::class, 'store']);
        Route::put('/coupons/{coupon}', [CouponController::class, 'update']);
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy']);

        // Pricing Rules (admin)
        Route::get('/pricing/rules', [PricingController::class, 'rules']);
        Route::post('/pricing/rules', [PricingController::class, 'storeRule']);
        Route::put('/pricing/rules/{rule}', [PricingController::class, 'updateRule']);
        Route::delete('/pricing/rules/{rule}', [PricingController::class, 'deleteRule']);
    });
});
