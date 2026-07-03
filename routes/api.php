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
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle.auth');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle.auth');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle.auth');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// Public data
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{car}', [CarController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);
Route::get('/cms', [CmsController::class, 'getCmsContent']);
Route::get('/cms/{key}', [CmsController::class, 'getCmsByKey']);
Route::post('/bookings/lookup', [BookingController::class, 'lookup'])->middleware('throttle:10,1');

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

    // Loyalty & Referral
    Route::get('/loyalty/balance', [LoyaltyController::class, 'balance']);
    Route::get('/loyalty/history', [LoyaltyController::class, 'history']);
    Route::get('/loyalty/tier', [LoyaltyController::class, 'tier']);
    Route::post('/referral/generate', [LoyaltyController::class, 'generateReferral']);
    Route::get('/referral/stats', [LoyaltyController::class, 'referralStats']);
    Route::post('/referral/apply', [LoyaltyController::class, 'applyReferral']);

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
    });
});
