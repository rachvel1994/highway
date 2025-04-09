<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'company'
    ];

    public function company_items(): HasMany
    {
        return $this->hasMany(CompanyItem::class, 'company_id', 'id');
    }
}
