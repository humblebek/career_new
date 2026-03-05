<?php

namespace App\Services;

use App\Models\CareerResult;
use App\Models\TestAttempt;

class CareerMatchingService
{
    /**
     * All supported careers.
     */
    public const CAREERS = [
        'Software Engineer',
        'Data Scientist',
        'Marketing Manager',
        'Teacher',
        'Doctor',
        'Artist',
    ];

    /**
     * Keyword → career weights map used for short_answer questions
     * when no career_weights are defined on the question itself.
     */
    protected array $keywordMap = [
        'programming'   => ['Software Engineer' => 3, 'Data Scientist' => 1],
        'coding'        => ['Software Engineer' => 3, 'Data Scientist' => 1],
        'software'      => ['Software Engineer' => 3],
        'computer'      => ['Software Engineer' => 2, 'Data Scientist' => 1],
        'algorithm'     => ['Software Engineer' => 2, 'Data Scientist' => 2],
        'data'          => ['Data Scientist' => 3, 'Software Engineer' => 1],
        'analysis'      => ['Data Scientist' => 3, 'Marketing Manager' => 1],
        'statistics'    => ['Data Scientist' => 3],
        'machine learning' => ['Data Scientist' => 3, 'Software Engineer' => 1],
        'marketing'     => ['Marketing Manager' => 3],
        'business'      => ['Marketing Manager' => 2],
        'sales'         => ['Marketing Manager' => 2],
        'strategy'      => ['Marketing Manager' => 2],
        'advertising'   => ['Marketing Manager' => 3],
        'teaching'      => ['Teacher' => 3],
        'education'     => ['Teacher' => 3],
        'students'      => ['Teacher' => 3],
        'classroom'     => ['Teacher' => 3],
        'helping'       => ['Teacher' => 2, 'Doctor' => 2],
        'medicine'      => ['Doctor' => 3],
        'health'        => ['Doctor' => 3],
        'medical'       => ['Doctor' => 3],
        'patient'       => ['Doctor' => 3],
        'clinical'      => ['Doctor' => 2],
        'art'           => ['Artist' => 3],
        'creative'      => ['Artist' => 3, 'Marketing Manager' => 1],
        'design'        => ['Artist' => 2, 'Software Engineer' => 1],
        'drawing'       => ['Artist' => 3],
        'music'         => ['Artist' => 3],
        'painting'      => ['Artist' => 3],
    ];

    /**
     * Generate a CareerResult for a completed TestAttempt.
     */
    public function generate(TestAttempt $testAttempt): CareerResult
    {
        $scores = array_fill_keys(self::CAREERS, 0.0);
        $answers = $testAttempt->answers()->with('question')->get();

        foreach ($answers as $answer) {
            $question = $answer->question;
            $type     = $question->question_type;
            $weights  = $question->career_weights; // nullable array

            switch ($type) {
                case 'multiple_choice':
                    $this->scoreMultipleChoice($scores, $answer, $question, $weights);
                    break;

                case 'scale':
                    $this->scoreScale($scores, $answer, $weights);
                    break;

                case 'short_answer':
                    $this->scoreShortAnswer($scores, $answer, $weights);
                    break;
            }
        }

        // Determine top career
        arsort($scores);
        $topCareer   = array_key_first($scores);
        $topScore    = $scores[$topCareer];
        $maxPossible = $this->maxPossibleScore($answers);
        $matchPct    = $maxPossible > 0
            ? (int) min(100, round(($topScore / $maxPossible) * 100))
            : 0;

        return CareerResult::create([
            'test_attempt_id'    => $testAttempt->id,
            'career_title'       => $topCareer,
            'career_description' => $this->getDescription($topCareer),
            'career_skills'      => $this->getSkills($topCareer),
            'career_paths'       => $this->getPaths($topCareer),
            'match_percentage'   => $matchPct,
            'detailed_analysis'  => $scores,
        ]);
    }

    // -------------------------------------------------------------------------
    // Scoring helpers
    // -------------------------------------------------------------------------

    /**
     * Score a multiple_choice answer.
     *
     * career_weights format for multiple_choice:
     *   { "options": [ {"Software Engineer":3,"Teacher":1}, ... ] }
     *   Each element corresponds by index to the question's options array.
     */
    protected function scoreMultipleChoice(array &$scores, $answer, $question, ?array $weights): void
    {
        $chosenText = $answer->answer_text;
        $options    = $question->options ?? [];
        $chosenIdx  = array_search($chosenText, $options);

        if ($weights && isset($weights['options'][$chosenIdx])) {
            // Use the per-option weight map
            $optionWeights = $weights['options'][$chosenIdx];
            foreach ($optionWeights as $career => $weight) {
                if (array_key_exists($career, $scores)) {
                    $scores[$career] += $weight;
                }
            }
            // Store a derived score on the answer (max weight for that option)
            $answer->score = max(array_values($optionWeights));
        } else {
            // Fallback: keyword scan the chosen option text
            $this->applyKeywordWeights($scores, strtolower($chosenText));
            $answer->score = 1;
        }

        $answer->saveQuietly();
    }

    /**
     * Score a scale answer (1–10).
     *
     * career_weights format for scale:
     *   { "careers": {"Software Engineer":0.5,"Data Scientist":0.8} }
     *   Each career gets: score × its weight coefficient.
     */
    protected function scoreScale(array &$scores, $answer, ?array $weights): void
    {
        $scale = (int) ($answer->score ?? 0);
        if ($scale < 1) {
            return;
        }

        if ($weights && isset($weights['careers'])) {
            // Apply per-career coefficients
            foreach ($weights['careers'] as $career => $coefficient) {
                if (array_key_exists($career, $scores)) {
                    $scores[$career] += $scale * (float) $coefficient;
                }
            }
        } else {
            // Fallback: only boost careers proportionally — no equal distribution
            // We give a tiny uniform boost so old questions still work
            foreach ($scores as $career => $_) {
                $scores[$career] += $scale * 0.05;
            }
        }
    }

    /**
     * Score a short_answer answer.
     *
     * career_weights format for short_answer:
     *   { "keywords": {"programming":{"Software Engineer":3},"data":{"Data Scientist":2}} }
     */
    protected function scoreShortAnswer(array &$scores, $answer, ?array $weights): void
    {
        $text = strtolower($answer->answer_text);
        $totalScore = 0;

        if ($weights && isset($weights['keywords'])) {
            foreach ($weights['keywords'] as $keyword => $careerMap) {
                if (str_contains($text, strtolower($keyword))) {
                    foreach ($careerMap as $career => $weight) {
                        if (array_key_exists($career, $scores)) {
                            $scores[$career] += $weight;
                            $totalScore      += $weight;
                        }
                    }
                }
            }
        } else {
            // Fallback: use global keyword map
            $totalScore = $this->applyKeywordWeights($scores, $text);
        }

        $answer->score = min(10, $totalScore);
        $answer->saveQuietly();
    }

    /**
     * Apply the global keyword map against a text, return total points added.
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

    /**
     * Calculate the theoretical maximum score across all answers for normalisation.
     */
    protected function maxPossibleScore($answers): float
    {
        // Assume max 3 points per question (highest option weight or keyword match)
        return count($answers) * 3;
    }

    // -------------------------------------------------------------------------
    // Career metadata
    // -------------------------------------------------------------------------

    public function getDescription(string $career): string
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

    public function getSkills(string $career): array
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

    public function getPaths(string $career): array
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

