<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'career_test_id',
        'question_text',
        'question_type',
        'options',
        'career_weights',
        'category',
        'importance',
        'order',
    ];

    protected $casts = [
        'options'        => 'array',
        'career_weights' => 'array',
        'importance'     => 'float',
    ];

    /**
     * Get the career test that owns the question.
     */
    public function careerTest(): BelongsTo
    {
        return $this->belongsTo(CareerTest::class);
    }

    /**
     * Get the answers for the question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
