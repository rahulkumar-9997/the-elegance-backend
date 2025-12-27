<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flyer extends Model
{
    use HasFactory;
    protected $table = 'flyers';
    protected $fillable = [
        'id',
        'title',
        'flyers_link',
        'image_file',
        'image_link',
        'status',
        'order',
    ];
}
