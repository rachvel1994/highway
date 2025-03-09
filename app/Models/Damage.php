<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Damage extends Model
{
    protected $fillable = [
        'equipment_id',
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

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }
}
