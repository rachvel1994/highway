<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkAssetDetail extends Model
{

    protected $fillable = [
        'work_asset_id',
        'job_type_id',
        'equipment_id',
        'personal_id',
        'time_spend',
        'completed_trip',
        'fuel_spend',
        'company_item_id',
        'company_item_quantity',
        'company_id',
        'store_id',
        'store_product_id',
        'store_product_quantity',
        'store_product_price',
        'person_salary',
        'person_salary_type',
        'person_worked_days',
        'person_worked_quantity',
        'person_salary_total',
    ];

    public function workAsset(): BelongsTo
    {
        return $this->belongsTo(WorkAsset::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'store_product_id');
    }

    public function getTimeSpendFormattedAttribute(): string
    {
        return number_format($this->time_spend, 2);
    }

    public function getFuelSpendFormattedAttribute(): string
    {
        return number_format($this->fuel_spend, 2);
    }

    public function getStoreProductQuantityFormattedAttribute(): string
    {
        return number_format($this->store_product_quantity, 2);
    }

    public function getStoreProductPriceFormattedAttribute(): string
    {
        return number_format($this->store_product_price, 2);
    }
}
