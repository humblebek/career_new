<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerTest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the questions for the career test.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get the test attempts for the career test.
     */
    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }
}
