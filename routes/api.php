<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

Route::get('/banner/video', [ApiController::class, 'singleVideo']);
Route::get('near-place-home', [ApiController::class, 'nearPlaceHome']);
Route::get('attraction-home', [ApiController::class, 'attractionHome']);
Route::get('flyers-home', [ApiController::class, 'flyersHome']);
Route::get('testimonials-home', [ApiController::class, 'testimonialsHome']);
Route::get('near-by-pace-list', [ApiController::class, 'nearByPlaceList']);
Route::get('near-by-pace-list/{slug}', [ApiController::class, 'nearByPlaceDetails']);
Route::get('banquets/onex-banquet', [ApiController::class, 'onexBanquet']);
Route::get('banquets/sapphire-banquet', [ApiController::class, 'sapphireBanquet']);
Route::get('tafri-lounge-image', [ApiController::class, 'tafriLoungeImages']);
Route::get('facilities', [ApiController::class, 'facilities']);
Route::get('album-gallery', [ApiController::class, 'albumGallery']);
