<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkAsset extends Model
{
    protected $fillable = [
        'street',
        'equipment_id',
        'personal_id',
        'company_id',
        'job_type_id',
        'measure_id',
        'traveled_km',
        'time_spend',
        'fuel_spend',
        'failure',
        'taken_items',
        'comment',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class, 'job_type_id', 'id');
    }

    public function measure(): BelongsTo
    {
        return $this->belongsTo(Measure::class, 'measure_id', 'id');
    }
}
