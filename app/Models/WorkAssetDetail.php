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
        'time_spend',
        'completed_trip',
        'fuel_id',
        'fuel_price',
        'fuel_spend',
        'fuel_total_price',
        'company_id',
        'item_id',
        'item_price',
        'item_quantity',
        'item_total_price',
        'store_id',
        'store_product_id',
        'product_price',
        'product_quantity',
        'product_price_total',
        'personal_id',
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

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }

    public function companyItem(): BelongsTo
    {
        return $this->belongsTo(CompanyItem::class, 'item_id');
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

    protected static function booted(): void
    {
        static::saved(function (WorkAssetDetail $detail) {
            $workAsset = $detail->workAsset()->with('details')->first();
            if ($workAsset) {
                $workAsset->recalculateTotal();
                $workAsset->refresh();
            }
        });
    }

}
