<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    protected $fillable = [
        'title',
        'damage',
        'detail_name',
        'quantity',
        'detail_price',
        'craft_price',
        'craftsman',
        'additional_expense',
        'comment',
    ];

    protected $casts = [
        'detail_price' => 'float',
        'craft_price' => 'float',
        'additional_expense' => 'float',
        'total_expense' => 'float',
    ];
}
