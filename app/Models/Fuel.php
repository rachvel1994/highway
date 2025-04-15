<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fuel extends Model
{
    protected $fillable = [
        'title',
        'price',
        'quantity',
        'remain',
        'total_price',
    ];

    protected $casts = [
        'price' => 'float',
        'total_price' => 'float',
        'quantity' => 'float',
        'remain' => 'float',
    ];
}
