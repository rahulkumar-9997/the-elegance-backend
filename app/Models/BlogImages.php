<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BlogImages extends Model
{
    protected $table = 'blog_images';
    protected $fillable = [
        'blog_id',
        'image', 
        'alt_text'
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }
}
