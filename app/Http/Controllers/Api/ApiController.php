<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\NearByPlace;
use App\Models\Flyer;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function singleVideo()
    {
        $banner = Banner::latest()->first();
        if (!$banner || !$banner->desktop_video) {
            return response()->json([
                'status' => false,
                'message' => 'No banner video found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => [
                'id'    => $banner->id,
                'video' => $banner->desktop_video,
            ]
        ]);
    }

    public function nearPlaceHome()
    {
        $nearByPlaces = NearByPlace::where('status', 1)->where('attractions_status', 0)
            ->orderBy('order')
            ->limit(8)
            ->get([
                'id',
                'title',
                'short_desc',
                'long_description',
                'image',
                'meta_title',
                'meta_description'
            ]);
        $nearByPlaces->transform(function ($item) {
            $item->image_url = asset('storage/nearby-places/' . $item->image);
            unset($item->image);
            return $item;
        });
        return response()->json([
            'status' => true,
            'count'  => $nearByPlaces->count(),
            'data'   => $nearByPlaces
        ]);
    }

    public function attractionHome()
    {
        $nearByPlaces = NearByPlace::where('status', 1)->where('attractions_status', 1)
            ->orderBy('order')
            ->limit(8)
            ->get([
                'id',
                'title',
                'short_desc',
                'long_description',
                'image',
                'meta_title',
                'meta_description'
            ]);
        $nearByPlaces->transform(function ($item) {
            $item->image_url = asset('storage/nearby-places/' . $item->image);
            unset($item->image);
            return $item;
        });
        return response()->json([
            'status' => true,
            'count'  => $nearByPlaces->count(),
            'data'   => $nearByPlaces
        ]);
    }

    public function flyersHome()
    {
        $flyers = Flyer::where('status', 1)
            ->orderBy('order')
            ->get([
                'id',
                'flyers_link',
                'image_file',
                'order'
            ]);

        $flyers->transform(function ($flyer) {
            return [
                'id' => $flyer->id,
                'link' => $flyer->flyers_link,
                'image_url' => asset('storage/flyers/' . $flyer->image_file),
                'order' => $flyer->order,
            ];
        });

        return response()->json([
            'status' => true,
            'count'  => $flyers->count(),
            'data'   => $flyers
        ]);
    }

    public function testimonialsHome()
    {
        $testimonials = Testimonial::where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $data = $testimonials->map(function ($t) {
            $visitDate = $t->visit_date
                ? Carbon::parse($t->visit_date)->format('F Y')
                : null;
            $ratingPercent = fn ($rating) => $rating ? ($rating / 5) * 100 : 0;
            return [
                'id' => $t->id,
                'title' => $t->title,
                'guest_type' => $t->guest_type,
                'visit_date' => $visitDate,
                'review_text' => $t->review_text,
                'ratings' => [
                    [
                        'label' => 'Value',
                        'score' => number_format($t->value_rating, 1),
                        'percent' => $ratingPercent($t->value_rating),
                    ],
                    [
                        'label' => 'Rooms',
                        'score' => number_format($t->rooms_rating, 1),
                        'percent' => $ratingPercent($t->rooms_rating),
                    ],
                    [
                        'label' => 'Location',
                        'score' => number_format($t->location_rating, 1),
                        'percent' => $ratingPercent($t->location_rating),
                    ],
                    [
                        'label' => 'Cleanliness',
                        'score' => number_format($t->cleanliness_rating, 1),
                        'percent' => $ratingPercent($t->cleanliness_rating),
                    ],
                    [
                        'label' => 'Service',
                        'score' => number_format($t->service_rating, 1),
                        'percent' => $ratingPercent($t->service_rating),
                    ],
                    [
                        'label' => 'Sleep Quality',
                        'score' => number_format($t->sleep_quality_rating, 1),
                        'percent' => $ratingPercent($t->sleep_quality_rating),
                    ],
                ],
            ];
        });
        return response()->json([
            'status' => true,
            'count' => $data->count(),
            'data' => $data
        ]);
    }
}
