<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'size',
        'shoe_id',
    ];
}
