<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\EnquiryController;

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
Route::get('home-blog', [ApiController::class, 'homeBlog']);
Route::get('blog', [ApiController::class, 'blogList']);
Route::get('blog/{slug}', [ApiController::class, 'blogDetails']);
Route::post('/enquiry', [EnquiryController::class, 'store']);
Route::get('home-enquiry', function () {
    return response()->json([
        'status'  => false,
        'message' => 'Invalid request method. Please submit enquiry using POST request.'
    ], 405);
});
Route::post('home-enquiry', [EnquiryController::class, 'homeEnquiryStore']);

