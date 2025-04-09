<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkAsset extends Model
{
    protected $fillable = [
        'street',
        'grand_total',
        'is_completed',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(WorkAssetDetail::class, 'work_asset_id');
    }
}
