<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'title',
        'guest_type',
        'visit_date',
        'review_text',
        'value_rating',
        'rooms_rating',
        'location_rating',
        'cleanliness_rating',
        'service_rating',
        'sleep_quality_rating',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'value_rating' => 'float',
        'rooms_rating' => 'float',
        'location_rating' => 'float',
        'cleanliness_rating' => 'float',
        'service_rating' => 'float',
        'sleep_quality_rating' => 'float',
        'status' => 'integer',
    ];
}
