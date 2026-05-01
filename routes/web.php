<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Catalog\ProductCatalogController;
use App\Http\Controllers\Catalog\ProductLandingAssetController;
use App\Http\Controllers\Catalog\ProductLandingPageController;
use App\Http\Controllers\Catalog\EventCatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardClientController;
use App\Http\Controllers\MyCourseController;
use App\Http\Controllers\MyCourseLessonController;
use App\Http\Controllers\MyEventAccessController;
use App\Http\Controllers\MyEventController;
use App\Http\Controllers\MyEpiChannelController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MyProductController;
use App\Http\Controllers\MyProductFileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReferralController;
use App\Http\Middleware\CaptureReferralFromRequest;

Route::view('/offline', 'offline')->name('offline');

Route::middleware([CaptureReferralFromRequest::class])
    ->get('/', HomeController::class)
    ->name('home');

Route::middleware([CaptureReferralFromRequest::class])->prefix('produk')->name('catalog.products.')->group(function () {
    Route::get('/', [ProductCatalogController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [ProductCatalogController::class, 'show'])->name('show');
});

Route::prefix('offer')->name('offer.')->group(function () {
    Route::get('/{product:slug}', [ProductLandingPageController::class, 'show'])->name('show');
    Route::get('/{product:slug}/ref/{epicCode}', [ProductLandingPageController::class, 'showAffiliate'])->name('affiliate');
});

Route::get('/offer-assets/{token}/{path}', [ProductLandingAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('offer-assets.show');

Route::middleware([CaptureReferralFromRequest::class])->prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventCatalogController::class, 'index'])->name('index');
    Route::get('/{event:slug}', [EventCatalogController::class, 'show'])->name('show');
});

Route::get('/r/{epicCode}', [ReferralController::class, 'redirect'])->name('referral.redirect');

Route::middleware([CaptureReferralFromRequest::class])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/{product:slug}', [CheckoutController::class, 'show'])->name('show');
    Route::post('/{product:slug}', [CheckoutController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/klien', [DashboardClientController::class, 'index'])->name('dashboard.clients.index');
    Route::get('/dashboard/klien/{client}', [DashboardClientController::class, 'show'])->name('dashboard.clients.show');
    Route::post('/dashboard/klien/{client}/notes', [DashboardClientController::class, 'storeNote'])->name('dashboard.clients.notes.store');
    Route::post('/dashboard/klien/{client}/follow-up', [DashboardClientController::class, 'markFollowUp'])->name('dashboard.clients.follow-up.store');

    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');

    Route::prefix('epi-channel')->name('epi-channel.')->group(function () {
        Route::get('/', [MyEpiChannelController::class, 'dashboard'])->name('dashboard');
        Route::get('/links', [MyEpiChannelController::class, 'links'])->name('links');
        Route::get('/products', [MyEpiChannelController::class, 'products'])->name('products');
        Route::get('/visits', [MyEpiChannelController::class, 'visits'])->name('visits');
        Route::get('/orders', [MyEpiChannelController::class, 'orders'])->name('orders');
        Route::get('/commissions', [MyEpiChannelController::class, 'commissions'])->name('commissions');
        Route::get('/payouts', [MyEpiChannelController::class, 'payouts'])->name('payouts');
        Route::get('/promo-assets', [MyEpiChannelController::class, 'promoAssets'])->name('promo-assets');
        Route::get('/profile', [MyEpiChannelController::class, 'profile'])->name('profile');
    });

    Route::prefix('produk-saya')->name('my-products.')->group(function () {
        Route::get('/', [MyProductController::class, 'index'])->name('index');
        Route::get('/{userProduct}', [MyProductController::class, 'show'])->name('show');

        Route::prefix('/{userProduct}/files/{productFile}')->name('files.')->group(function () {
            Route::get('/view', [MyProductFileController::class, 'view'])->name('view');
            Route::get('/download', [MyProductFileController::class, 'download'])->name('download');
            Route::get('/open', [MyProductFileController::class, 'openExternal'])->name('open');
        });
    });

    Route::prefix('kelas-saya')->name('my-courses.')->group(function () {
        Route::get('/', [MyCourseController::class, 'index'])->name('index');
        Route::get('/{userProduct}', [MyCourseController::class, 'show'])->name('show');

        Route::prefix('/{userProduct}/lessons/{courseLesson}')->name('lessons.')->group(function () {
            Route::get('/', [MyCourseLessonController::class, 'show'])->name('show');
            Route::post('/complete', [MyCourseLessonController::class, 'complete'])->name('complete');
            Route::get('/download', [MyCourseLessonController::class, 'download'])->name('download');
            Route::get('/attachments/{attachment}/download', [MyCourseLessonController::class, 'downloadAttachment'])->name('attachments.download');
            Route::get('/open', [MyCourseLessonController::class, 'openExternal'])->name('open');
        });
    });

    Route::prefix('event-saya')->name('my-events.')->group(function () {
        Route::get('/', [MyEventController::class, 'index'])->name('index');
        Route::get('/{eventRegistration}', [MyEventController::class, 'show'])->name('show');
        Route::get('/{eventRegistration}/join', [MyEventAccessController::class, 'join'])->name('join');
        Route::get('/{eventRegistration}/replay', [MyEventAccessController::class, 'replay'])->name('replay');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('/{payment}/proof', [PaymentController::class, 'proof'])->name('proof.show');
        Route::post('/{payment}/proof', [PaymentController::class, 'storeProof'])->name('proof.store');
    });
});

require __DIR__.'/settings.php';
