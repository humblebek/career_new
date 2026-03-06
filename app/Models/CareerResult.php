<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerResult extends Model
{
    protected $fillable = [
        'test_attempt_id',
        'career_id',
        'career_title',
        'career_description',
        'career_skills',
        'career_paths',
        'match_percentage',
        'detailed_analysis',
        'category_scores',
    ];

    protected $casts = [
        'career_skills'     => 'array',
        'career_paths'      => 'array',
        'detailed_analysis' => 'array',
        'category_scores'   => 'array',
    ];

    /**
     * Get the career that this result matched.
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Career::class);
    }

    /**
     * Get the test attempt that owns the career result.
     */
    public function testAttempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class);
    }
}
