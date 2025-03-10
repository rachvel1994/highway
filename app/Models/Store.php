<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'title',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'store_id', 'id');
    }
}
