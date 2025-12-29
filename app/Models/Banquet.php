<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Banquet extends Model
{
    use HasFactory;
    protected $table = 'banquets';
    protected $fillable = [
        'title',
        'slug',
        'heading_name',
        'top_content',
        'bottom_content',
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
    /**
     * Relationship: Banquet has many images
     */
    public function images()
    {
        return $this->hasMany(BanquetImage::class, 'banquets_id')->orderBy('order');
    }
}
