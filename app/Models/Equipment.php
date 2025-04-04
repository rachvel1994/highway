<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $fillable = [
        'equipment',
        'type',
        'price',
    ];

    protected $casts = [
        'price' => 'float'
    ];

    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class, 'equipment_id', 'id');
    }
}
