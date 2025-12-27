<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'description',
        'status',
    ];

    /**
     * One Album has many Gallery images
     */
    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
}
