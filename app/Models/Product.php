<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'title',
        'quantity',
        'category_id',
        'measure_id',
        'price',
        'total_price',
        'comment',
    ];

    protected $casts = [
        'price' => 'float',
        'total_price' => 'float',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')->where('type_id', 1);
    }

    public function measure(): BelongsTo
    {
        return $this->belongsTo(Measure::class, 'measure_id', 'id');
    }

    public function getTitleWithStoreAttribute(): string
    {
        return "{$this->title} ({$this->store?->store})";
    }
}
