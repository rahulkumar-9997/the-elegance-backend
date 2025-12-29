<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\LoginController;
use App\Http\Controllers\Backend\ForgotPasswordController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\CacheController;
use  App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\AlbumController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\NearByPlaceController;
use App\Http\Controllers\Backend\FlyersController;
use App\Http\Controllers\Backend\BanquetsController;
use App\Http\Controllers\Backend\BanquetsImagesController;
use App\Http\Controllers\Backend\TafriImagesController;
use App\Http\Controllers\Backend\TestimonialsController;
use App\Http\Controllers\Backend\FacilitiesController;

Route::prefix('admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::get('forget/password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password');
    Route::post('forget.password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.submit');

    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
    Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/get-daily-visitors', [DashboardController::class, 'getDailyVisitors'])->name('get-daily-visitors');

    Route::get('/clear-cache', [CacheController::class, 'clearCache'])->name('clear-cache');
    Route::resource('manage-banner', BannerController::class);
    Route::resource('manage-album', AlbumController::class);
    Route::resource('manage-gallery', GalleryController::class);
    Route::resource('manage-near-by-place', NearByPlaceController::class);
    Route::prefix('manage-near-by-place')->group(function () {
        Route::post('/{id}/order-up', [NearByPlaceController::class, 'orderUp'])->name('manage-near-by-place.order-up');
        Route::post('/{id}/order-down', [NearByPlaceController::class, 'orderDown'])->name('manage-near-by-place.order-down');
        Route::post('/{id}/add-to-attractions', [NearByPlaceController::class, 'addToAttractions'])->name('manage-near-by-place.add-to-attractions');
        Route::put('/{id}/remove-from-attractions', [NearByPlaceController::class, 'removeFromAttractions'])->name('manage-near-by-place.remove-from-attractions');
    });
    Route::resource('manage-flyers', FlyersController::class);
    Route::prefix('manage-flyers')->group(function () {
        Route::post('/{id}/order-up', [NearByPlaceController::class, 'orderUp'])->name('manage-flyers.order-up');
        Route::post('/{id}/order-down', [NearByPlaceController::class, 'orderDown'])->name('manage-flyers.order-down');
    });
    Route::resource('manage-banquets', BanquetsController::class);
    Route::prefix('banquet-images')->group(function () {
        Route::get('/create/{id}', [BanquetsImagesController::class, 'createForBanquet'])
            ->name('manage-banquet-images.create');
        Route::post('/', [BanquetsImagesController::class, 'store'])
            ->name('manage-banquet-images.store');
        Route::get('/{id}', [BanquetsImagesController::class, 'viewBanquetImages'])
            ->name('manage-banquet-images.index');
        Route::delete('/{id}', [BanquetsImagesController::class, 'destroyImage'])
            ->name('manage-banquet-images.destroy');
       
    });
    Route::resource('manage-tafri-lounge-image', TafriImagesController::class);
    Route::prefix('tafri-lounge-images')->group(function () {        
        Route::post('/{id}/order-up', [TafriImagesController::class, 'orderUp'])
            ->name('manage-tafri-lounge-image.order-up');
        Route::post('/{id}/order-down', [TafriImagesController::class, 'orderDown'])
            ->name('manage-tafri-lounge-image.order-down');
    });
    Route::resource('manage-testimonials', TestimonialsController::class);
    Route::resource('manage-facilities', FacilitiesController::class);
});
