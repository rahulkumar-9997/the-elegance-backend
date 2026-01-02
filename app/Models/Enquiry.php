<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Enquiry extends Model
{
    use HasFactory;
    protected $fillable = [
        'enquiry_for',
        'name',        
        'email',
        'subject',
        'message',
        'ip',
    ];
}
