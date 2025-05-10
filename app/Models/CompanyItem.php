<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyItem extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'quantity',
        'category_id',
        'measure_id',
        'price',
        'total_price',
        'comment',
        'buy_at',
        'sold_at',
    ];

    protected $casts = [
        'price' => 'float',
        'total_price' => 'float',
        'total_expense' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')->where('type_id', 2);
    }

    public function measure(): BelongsTo
    {
        return $this->belongsTo(Measure::class, 'measure_id', 'id');
    }

    public function getTitleWithCompanyAttribute(): string
    {
        return "{$this->title} ({$this->company?->company})";
    }
}
