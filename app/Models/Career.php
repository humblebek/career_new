<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Career extends Model
{
    protected $fillable = [
        'title',
        'description',
        'skills',
        'paths',
        'is_active',
    ];

    protected $casts = [
        'skills'    => 'array',
        'paths'     => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get career results that matched this career.
     */
    public function careerResults(): HasMany
    {
        return $this->hasMany(CareerResult::class);
    }
}

