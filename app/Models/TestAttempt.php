<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'career_test_id',
        'started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the test attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the career test that owns the test attempt.
     */
    public function careerTest(): BelongsTo
    {
        return $this->belongsTo(CareerTest::class);
    }

    /**
     * Get the answers for the test attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the career result for the test attempt.
     */
    public function careerResult(): HasOne
    {
        return $this->hasOne(CareerResult::class);
    }
}
