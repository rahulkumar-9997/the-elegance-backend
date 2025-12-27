<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'album_id',
        'gallery_image',
        'title',
        'description',
    ];

    /**
     * Gallery belongs to an Album
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
