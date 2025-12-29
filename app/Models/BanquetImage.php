<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanquetImage extends Model
{
    use HasFactory;
    protected $table = 'banquets_images';
    protected $fillable = [
        'banquets_id',
        'image_file',
        'order',
    ];
    /**
     * Relationship: Image belongs to a banquet
     */
    public function banquet()
    {
        return $this->belongsTo(Banquet::class, 'banquets_id');
    }
}
