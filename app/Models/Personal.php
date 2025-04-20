<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    protected $fillable = [
        'full_name',
    ];

    public function workAssetDetails(): HasMany
    {
        return $this->hasMany(WorkAssetDetail::class, 'personal_id');
    }
}
