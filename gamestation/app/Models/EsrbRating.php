<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EsrbRating extends Model
{
    use HasFactory;

    protected $table = 'esrb_ratings';

    protected $fillable = [
        'code',
        'name',
        'description',
        'age_group',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
