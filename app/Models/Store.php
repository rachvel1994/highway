<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    protected $fillable = [
        'title',
        'quantity',
        'category_id',
        'measure_id',
        'price',
        'comment',
    ];

    protected $casts = [
        'price' => 'float',
        'total_expense' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function measure(): BelongsTo
    {
        return $this->belongsTo(Measure::class, 'measure_id', 'id');
    }
}
