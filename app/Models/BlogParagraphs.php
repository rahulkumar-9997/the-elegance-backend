<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BlogParagraphs extends Model
{
    protected $table = 'blog_paragraphs';
    protected $fillable = [
        'blog_id',
        'title', 
        'content',
        'image',
        'sort_order'
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }
}
