<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    protected $fillable = [
        'factory',
        'damage',
        'detail_name',
        'quantity',
        'detail_price',
        'craft_price',
        'craftsman',
        'additional_expense',
        'total_price',
        'comment',
    ];

    protected $casts = [
        'detail_price' => 'float',
        'craft_price' => 'float',
        'additional_expense' => 'float',
        'total_price' => 'float',
    ];
}
