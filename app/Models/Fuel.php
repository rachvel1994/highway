<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fuel extends Model
{
    protected $fillable = [
        'title',
        'price',
        'quantity',
        'remain'
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'float',
        'remain' => 'float',
    ];
}
