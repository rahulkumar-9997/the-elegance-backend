<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class NearByPlace extends Model
{
    use HasFactory;
    protected $table = 'near_by_places';
    protected $fillable = [
        'id',
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'image',
        'page_image',
        'short_desc',
        'long_description',
        'status',
        'order',
        'attractions_status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'attractions_status' => 'boolean',
        'order' => 'integer',
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
}
