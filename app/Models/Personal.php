<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $fillable = [
        'full_name',
        'salary',
        'comment'
    ];

    protected $casts = [
        'salary' => 'float',
    ];
}
