<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class Blog extends Model
{
    protected $table = 'blogs';
    protected $fillable = [
        'title',
        'slug', 
        'short_desc',
        'content', 
        'meta_title',
        'meta_description',
        'featured_image',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($page) {
            $page->slug = $page->createSlug($page->title);
        });
    }

    private function createSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlogImages::class, 'blog_id');
    }
    public function paragraphs(): HasMany
    {
        return $this->hasMany(BlogParagraphs::class, 'blog_id')->orderBy('sort_order');
    }
    
}
