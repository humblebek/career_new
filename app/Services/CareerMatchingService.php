<?php

namespace App\Services;

use App\Models\Career;
use App\Models\CareerResult;
use App\Models\TestAttempt;

class CareerMatchingService
{
    /**
     * Negation words — if a keyword is preceded by one of these within a short
     * window the keyword match is inverted (penalised instead of rewarded).
     */
    protected const NEGATION_WORDS = [
        'not', "n't", 'no', 'never', 'hardly', 'hate', 'dislike',
        'dont', "don't", "isn't", "aren't", "can't", "won't",
        "wouldn't", "shouldn't", "couldn't", 'neither', 'nor',
        'without', 'lack', 'avoid',
    ];

    /**
     * Maximum look-behind distance (in words) when checking for negation.
     */
    protected const NEGATION_WINDOW = 4;

    /**
     * Global keyword → career weights used as fallback when a question has no
     * career_weights defined.
     */
    protected array $keywordMap = [
        'programming'      => ['Software Engineer' => 3, 'Data Scientist' => 1],
        'coding'           => ['Software Engineer' => 3, 'Data Scientist' => 1],
        'software'         => ['Software Engineer' => 3],
        'computer'         => ['Software Engineer' => 2, 'Data Scientist' => 1],
        'algorithm'        => ['Software Engineer' => 2, 'Data Scientist' => 2],
        'data'             => ['Data Scientist' => 3, 'Software Engineer' => 1],
        'analysis'         => ['Data Scientist' => 3, 'Marketing Manager' => 1],
        'statistics'       => ['Data Scientist' => 3],
        'machine learning' => ['Data Scientist' => 3, 'Software Engineer' => 1],
        'marketing'        => ['Marketing Manager' => 3],
        'business'         => ['Marketing Manager' => 2],
        'sales'            => ['Marketing Manager' => 2],
        'strategy'         => ['Marketing Manager' => 2],
        'advertising'      => ['Marketing Manager' => 3],
        'teaching'         => ['Teacher' => 3],
        'education'        => ['Teacher' => 3],
        'students'         => ['Teacher' => 3],
        'classroom'        => ['Teacher' => 3],
        'helping'          => ['Teacher' => 2, 'Doctor' => 2],
        'medicine'         => ['Doctor' => 3],
        'health'           => ['Doctor' => 3],
        'medical'          => ['Doctor' => 3],
        'patient'          => ['Doctor' => 3],
        'clinical'         => ['Doctor' => 2],
        'art'              => ['Artist' => 3],
        'creative'         => ['Artist' => 3, 'Marketing Manager' => 1],
        'design'           => ['Artist' => 2, 'Software Engineer' => 1],
        'drawing'          => ['Artist' => 3],
        'music'            => ['Artist' => 3],
        'painting'         => ['Artist' => 3],
    ];

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Generate a CareerResult for a completed TestAttempt.
     *
     * Algorithm overview:
     *  1. Load active careers from the database.
     *  2. For every answer, compute a **normalised** per-career score (0–1)
     *     so that each question type contributes on the same scale.
     *  3. Multiply the normalised score by the question's importance weight.
     *  4. Accumulate scores per career AND per category.
     *  5. Pick the top-scoring career, compute a match percentage, persist.
     */
    public function generate(TestAttempt $testAttempt): CareerResult
    {
        $careers     = $this->loadCareers();
        $careerNames = array_keys($careers);
        $scores      = array_fill_keys($careerNames, 0.0);
        $maxScores   = array_fill_keys($careerNames, 0.0);

        // Category tracking: category → { career → score }
        $categoryScores = [];

        $answers = $testAttempt->answers()->with('question')->get();

        foreach ($answers as $answer) {
            $question   = $answer->question;
            $type       = $question->question_type;
            $weights    = $question->career_weights;
            $importance = (float) ($question->importance ?: 1.0);
            $category   = $question->category ?? 'general';

            // --- raw score per career for this question (un-normalised) ---
            $rawScores = array_fill_keys($careerNames, 0.0);

            switch ($type) {
                case 'multiple_choice':
                    $rawScores = $this->scoreMultipleChoice($rawScores, $answer, $question, $weights);
                    break;
                case 'scale':
                    $rawScores = $this->scoreScale($rawScores, $answer, $weights);
                    break;
                case 'short_answer':
                    $rawScores = $this->scoreShortAnswer($rawScores, $answer, $weights);
                    break;
            }

            // --- compute this question's theoretical max ---
            $qMax = $this->questionMaxScore($question);

            // --- normalise to 0–1, then apply importance ---
            foreach ($careerNames as $career) {
                $normalised = $qMax > 0 ? ($rawScores[$career] / $qMax) : 0.0;
                $normalised = max(0.0, min(1.0, $normalised)); // clamp
                $weighted   = $normalised * $importance;

                $scores[$career]    += $weighted;
                $maxScores[$career] += $importance;

                // Category accumulation
                if (!isset($categoryScores[$category])) {
                    $categoryScores[$category] = array_fill_keys($careerNames, 0.0);
                }
                $categoryScores[$category][$career] += $weighted;
            }
        }

        // --- determine top career ---
        arsort($scores);
        $topCareerName = array_key_first($scores);
        $topScore      = $scores[$topCareerName];

        // Match percentage: how close the top career is to its theoretical max
        $topMax   = $maxScores[$topCareerName] ?: 1;
        $matchPct = (int) min(100, round(($topScore / $topMax) * 100));

        // Convert raw scores to percentages for detailed_analysis
        $detailedAnalysis = [];
        foreach ($scores as $career => $score) {
            $max = $maxScores[$career] ?: 1;
            $detailedAnalysis[$career] = round(($score / $max) * 100, 1);
        }
        arsort($detailedAnalysis);

        // Convert category scores to percentages
        $categoryAnalysis = [];
        foreach ($categoryScores as $cat => $catCareers) {
            foreach ($catCareers as $career => $score) {
                $max = $maxScores[$career] ?: 1;
                $categoryAnalysis[$cat][$career] = round(($score / $max) * 100, 1);
            }
        }

        // --- look up Career model for metadata ---
        $careerModel = $careers[$topCareerName] ?? null;

        return CareerResult::create([
            'test_attempt_id'    => $testAttempt->id,
            'career_id'          => $careerModel?->id,
            'career_title'       => $topCareerName,
            'career_description' => $careerModel?->description ?? $this->getFallbackDescription($topCareerName),
            'career_skills'      => $careerModel?->skills      ?? $this->getFallbackSkills($topCareerName),
            'career_paths'       => $careerModel?->paths       ?? $this->getFallbackPaths($topCareerName),
            'match_percentage'   => $matchPct,
            'detailed_analysis'  => $detailedAnalysis,
            'category_scores'    => $categoryAnalysis,
        ]);
    }

    // =========================================================================
    // Scoring helpers — each returns an array of raw (un-normalised) scores
    // =========================================================================

    /**
     * Score a multiple_choice answer.
     *
     * career_weights format: { "options": [ {"Software Engineer":3,"Teacher":1}, ... ] }
     */
    protected function scoreMultipleChoice(array $scores, $answer, $question, ?array $weights): array
    {
        $chosenText = $answer->answer_text;
        $options    = $question->options ?? [];
        $chosenIdx  = array_search($chosenText, $options);

        if ($weights && isset($weights['options'][$chosenIdx])) {
            $optionWeights = $weights['options'][$chosenIdx];
            foreach ($optionWeights as $career => $weight) {
                if (array_key_exists($career, $scores)) {
                    $scores[$career] += $weight;
                }
            }
            $answer->score = max(array_values($optionWeights));
        } else {
            $this->applyKeywordWeights($scores, strtolower($chosenText));
            $answer->score = 1;
        }

        $answer->saveQuietly();

        return $scores;
    }

    /**
     * Score a scale answer (1–10).
     *
     * career_weights format: { "careers": {"Software Engineer":0.5,"Data Scientist":0.8} }
     */
    protected function scoreScale(array $scores, $answer, ?array $weights): array
    {
        $scale = (int) ($answer->score ?? 0);
        if ($scale < 1) {
            return $scores;
        }

        if ($weights && isset($weights['careers'])) {
            foreach ($weights['careers'] as $career => $coefficient) {
                if (array_key_exists($career, $scores)) {
                    $scores[$career] += $scale * (float) $coefficient;
                }
            }
        } else {
            foreach ($scores as $career => $_) {
                $scores[$career] += $scale * 0.05;
            }
        }

        return $scores;
    }

    /**
     * Score a short_answer answer with **negation detection**.
     *
     * career_weights format: { "keywords": {"programming":{"Software Engineer":3}} }
     */
    protected function scoreShortAnswer(array $scores, $answer, ?array $weights): array
    {
        $text       = strtolower($answer->answer_text);
        $totalScore = 0;

        if ($weights && isset($weights['keywords'])) {
            foreach ($weights['keywords'] as $keyword => $careerMap) {
                if (str_contains($text, strtolower($keyword))) {
                    $negated    = $this->isNegated($text, strtolower($keyword));
                    $multiplier = $negated ? -0.5 : 1.0;

                    foreach ($careerMap as $career => $weight) {
                        if (array_key_exists($career, $scores)) {
                            $scores[$career] += $weight * $multiplier;
                            $totalScore      += $weight;
                        }
                    }
                }
            }
        } else {
            $totalScore = $this->applyKeywordWeightsWithNegation($scores, $text);
        }

        $answer->score = min(10, max(0, $totalScore));
        $answer->saveQuietly();

        return $scores;
    }

    // =========================================================================
    // Negation detection
    // =========================================================================

    /**
     * Adversative conjunctions that reset/cancel a preceding negation.
     * If one of these appears between the negation word and the keyword,
     * the keyword is NOT considered negated.
     */
    protected const ADVERSATIVE_CONJUNCTIONS = [
        'but', 'however', 'although', 'though', 'yet', 'still',
        'whereas', 'while', 'instead', 'rather', 'except',
    ];

    /**
     * Check whether a keyword occurrence in $text is preceded by a negation word
     * within NEGATION_WINDOW words, unless an adversative conjunction appears
     * between the negation word and the keyword (which resets the scope).
     */
    protected function isNegated(string $text, string $keyword): bool
    {
        $pos = strpos($text, $keyword);
        if ($pos === false) {
            return false;
        }

        // Grab up to NEGATION_WINDOW words before the keyword
        $before      = substr($text, 0, $pos);
        $beforeWords = preg_split('/\s+/', trim($before));
        $window      = array_slice($beforeWords, -self::NEGATION_WINDOW);

        // Walk the window in reverse (closest word to keyword first).
        // If we hit an adversative conjunction before a negation word,
        // the negation is cancelled.
        foreach (array_reverse($window) as $word) {
            $cleaned = preg_replace('/[^a-z\']/', '', $word);

            // Adversative conjunction resets negation scope
            if (in_array($cleaned, self::ADVERSATIVE_CONJUNCTIONS, true)) {
                return false;
            }

            if (in_array($cleaned, self::NEGATION_WORDS, true)) {
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // Keyword helpers
    // =========================================================================

    /**
     * Apply global keyword map with negation awareness, return total score.
     */
    protected function applyKeywordWeightsWithNegation(array &$scores, string $text): int
    {
        $total = 0;
        foreach ($this->keywordMap as $keyword => $careerWeights) {
            if (str_contains($text, $keyword)) {
                $negated    = $this->isNegated($text, $keyword);
                $multiplier = $negated ? -0.5 : 1.0;

                foreach ($careerWeights as $career => $weight) {
                    if (array_key_exists($career, $scores)) {
                        $scores[$career] += $weight * $multiplier;
                        $total           += $weight;
                    }
                }
            }
        }
        return $total;
    }

    /**
     * Simple keyword application without negation (used for MC fallback).
     */
    protected function applyKeywordWeights(array &$scores, string $text): int
    {
        $total = 0;
        foreach ($this->keywordMap as $keyword => $careerWeights) {
            if (str_contains($text, $keyword)) {
                foreach ($careerWeights as $career => $weight) {
                    if (array_key_exists($career, $scores)) {
                        $scores[$career] += $weight;
                        $total           += $weight;
                    }
                }
            }
        }
        return $total;
    }

    // =========================================================================
    // Per-question maximum score calculation
    // =========================================================================

    /**
     * Calculate the theoretical maximum raw score a single question can
     * contribute to **any one career**.  Used for 0–1 normalisation.
     */
    protected function questionMaxScore($question): float
    {
        $weights = $question->career_weights;
        $type    = $question->question_type;

        switch ($type) {
            case 'multiple_choice':
                return $this->mcMaxScore($question, $weights);
            case 'scale':
                return $this->scaleMaxScore($weights);
            case 'short_answer':
                return $this->shortAnswerMaxScore($weights);
            default:
                return 3.0;
        }
    }

    /**
     * MC max = the highest single-career weight across ALL options.
     */
    protected function mcMaxScore($question, ?array $weights): float
    {
        if ($weights && isset($weights['options'])) {
            $max = 0;
            foreach ($weights['options'] as $optionWeights) {
                if (is_array($optionWeights)) {
                    foreach ($optionWeights as $w) {
                        $max = max($max, $w);
                    }
                }
            }
            return max($max, 1);
        }
        return 3.0;
    }

    /**
     * Scale max = 10 × highest coefficient.
     */
    protected function scaleMaxScore(?array $weights): float
    {
        if ($weights && isset($weights['careers'])) {
            $maxCoeff = max(array_values($weights['careers']));
            return 10.0 * max($maxCoeff, 0.01);
        }
        return 0.5;
    }

    /**
     * Short-answer max = sum of all keyword weights for the highest-scoring career.
     */
    protected function shortAnswerMaxScore(?array $weights): float
    {
        if ($weights && isset($weights['keywords'])) {
            $careerTotals = [];
            foreach ($weights['keywords'] as $keyword => $careerMap) {
                foreach ($careerMap as $career => $w) {
                    $careerTotals[$career] = ($careerTotals[$career] ?? 0) + $w;
                }
            }
            return empty($careerTotals) ? 3.0 : max($careerTotals);
        }
        return 6.0;
    }

    // =========================================================================
    // Career loading from database
    // =========================================================================

    /**
     * Load active careers keyed by title.
     *
     * @return array<string, Career>
     */
    protected function loadCareers(): array
    {
        $careers = Career::where('is_active', true)->get();

        if ($careers->isEmpty()) {
            return $this->fallbackCareers();
        }

        $map = [];
        foreach ($careers as $career) {
            $map[$career->title] = $career;
        }
        return $map;
    }

    /**
     * Fallback career list when DB has no careers yet.
     */
    protected function fallbackCareers(): array
    {
        $fallback = [
            'Software Engineer', 'Data Scientist', 'Marketing Manager',
            'Teacher', 'Doctor', 'Artist',
        ];

        $map = [];
        foreach ($fallback as $title) {
            $career = new Career();
            $career->title       = $title;
            $career->description = $this->getFallbackDescription($title);
            $career->skills      = $this->getFallbackSkills($title);
            $career->paths       = $this->getFallbackPaths($title);
            $map[$title] = $career;
        }
        return $map;
    }

    // =========================================================================
    // Public metadata helpers (backward compatible)
    // =========================================================================

    public function getDescription(string $career): string
    {
        $model = Career::where('title', $career)->first();
        return $model?->description ?? $this->getFallbackDescription($career);
    }

    public function getSkills(string $career): array
    {
        $model = Career::where('title', $career)->first();
        return $model?->skills ?? $this->getFallbackSkills($career);
    }

    public function getPaths(string $career): array
    {
        $model = Career::where('title', $career)->first();
        return $model?->paths ?? $this->getFallbackPaths($career);
    }

    // =========================================================================
    // Fallback metadata
    // =========================================================================

    protected function getFallbackDescription(string $career): string
    {
        return [
            'Software Engineer'  => 'Software engineers design, develop, and maintain software applications. They work with programming languages, databases, and various technologies to create solutions for businesses and users.',
            'Data Scientist'     => 'Data scientists analyze complex data to help organizations make informed decisions. They use statistical methods, machine learning, and programming to extract insights from data.',
            'Marketing Manager'  => 'Marketing managers develop and implement marketing strategies to promote products or services. They oversee campaigns, analyze market trends, and work with creative teams.',
            'Teacher'            => 'Teachers educate and inspire students in various subjects. They create lesson plans, assess student progress, and help develop critical thinking and problem-solving skills.',
            'Doctor'             => 'Doctors diagnose and treat illnesses, injuries, and medical conditions. They work to improve patient health and well-being through medical care and treatment.',
            'Artist'             => 'Artists create visual, musical, or performing works of art. They express ideas, emotions, and concepts through various artistic mediums and techniques.',
        ][$career] ?? 'A career that matches your interests and skills.';
    }

    protected function getFallbackSkills(string $career): array
    {
        return [
            'Software Engineer'  => ['Programming', 'Problem Solving', 'System Design', 'Database Management', 'Version Control'],
            'Data Scientist'     => ['Statistics', 'Machine Learning', 'Data Analysis', 'Programming', 'Data Visualization'],
            'Marketing Manager'  => ['Strategic Planning', 'Communication', 'Market Research', 'Digital Marketing', 'Project Management'],
            'Teacher'            => ['Communication', 'Patience', 'Subject Knowledge', 'Classroom Management', 'Assessment'],
            'Doctor'             => ['Medical Knowledge', 'Diagnosis', 'Patient Care', 'Communication', 'Critical Thinking'],
            'Artist'             => ['Creativity', 'Visual Design', 'Technical Skills', 'Artistic Vision', 'Communication'],
        ][$career] ?? ['Communication', 'Problem Solving', 'Creativity'];
    }

    protected function getFallbackPaths(string $career): array
    {
        return [
            'Software Engineer'  => ['Junior Developer', 'Senior Developer', 'Tech Lead', 'Software Architect', 'CTO'],
            'Data Scientist'     => ['Junior Data Scientist', 'Data Scientist', 'Senior Data Scientist', 'Data Science Manager', 'Chief Data Officer'],
            'Marketing Manager'  => ['Marketing Coordinator', 'Marketing Specialist', 'Marketing Manager', 'Senior Marketing Manager', 'CMO'],
            'Teacher'            => ['Assistant Teacher', 'Teacher', 'Senior Teacher', 'Department Head', 'Principal'],
            'Doctor'             => ['Resident', 'Attending Physician', 'Specialist', 'Department Head', 'Chief Medical Officer'],
            'Artist'             => ['Freelance Artist', 'Studio Artist', 'Art Director', 'Creative Director', 'Gallery Owner'],
        ][$career] ?? ['Entry Level', 'Mid Level', 'Senior Level', 'Management', 'Executive'];
    }
}

