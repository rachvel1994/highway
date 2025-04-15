<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    protected $fillable = [
        'full_name',
        'salary',
        'salary_type',
        'worked_days',
        'comment'
    ];

    protected $casts = [
        'salary' => 'float',
    ];

    public function workAssetDetails(): HasMany
    {
        return $this->hasMany(WorkAssetDetail::class, 'person_id');
    }
}
