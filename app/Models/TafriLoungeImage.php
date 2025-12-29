<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TafriLoungeImage extends Model
{
    use HasFactory;
    protected $table = 'tafri_lounge_images';
    protected $fillable = [
        'title',
        'image_file',
        'order',
    ];
}
