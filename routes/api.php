<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

Route::get('/banner/video', [ApiController::class, 'singleVideo']);
Route::get('near-place-home', [ApiController::class, 'nearPlaceHome']);
Route::get('attraction-home', [ApiController::class, 'attractionHome']);
Route::get('flyers-home', [ApiController::class, 'flyersHome']);
Route::get('testimonials-home', [ApiController::class, 'testimonialsHome']);
