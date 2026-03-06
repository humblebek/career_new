<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Career;
use App\Models\CareerResult;
use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\CareerMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    private CareerMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CareerMatchingService();
        $this->seedCareers();
    }

    /**
     * Seed careers into the database so the service can load them.
     */
    private function seedCareers(): void
    {
        $careers = [
            ['title' => 'Software Engineer', 'description' => 'Builds software.', 'skills' => ['Programming'], 'paths' => ['Junior Developer']],
            ['title' => 'Data Scientist', 'description' => 'Analyzes data.', 'skills' => ['Statistics'], 'paths' => ['Junior Data Scientist']],
            ['title' => 'Marketing Manager', 'description' => 'Manages marketing.', 'skills' => ['Communication'], 'paths' => ['Marketing Coordinator']],
            ['title' => 'Teacher', 'description' => 'Educates students.', 'skills' => ['Patience'], 'paths' => ['Assistant Teacher']],
            ['title' => 'Doctor', 'description' => 'Treats patients.', 'skills' => ['Medical Knowledge'], 'paths' => ['Resident']],
            ['title' => 'Artist', 'description' => 'Creates art.', 'skills' => ['Creativity'], 'paths' => ['Freelance Artist']],
        ];

        foreach ($careers as $c) {
            Career::firstOrCreate(['title' => $c['title']], $c);
        }
    }

    // -------------------------------------------------------------------------
    // Helper: create a test attempt with questions and answers
    // -------------------------------------------------------------------------

    private function makeAttempt(array $questions): TestAttempt
    {
        $user = User::factory()->create(['role' => 'student']);
        $careerTest = CareerTest::create([
            'title' => 'Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);
        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        foreach ($questions as $i => $q) {
            $question = Question::create([
                'career_test_id' => $careerTest->id,
                'question_text' => $q['text'],
                'question_type' => $q['type'],
                'options' => $q['options'] ?? null,
                'career_weights' => $q['career_weights'] ?? null,
                'category' => $q['category'] ?? 'general',
                'importance' => $q['importance'] ?? 1.0,
                'order' => $i + 1,
            ]);
            Answer::create([
                'test_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_text' => $q['answer'],
                'score' => $q['score'] ?? null,
            ]);
        }

        return $attempt;
    }

    // -------------------------------------------------------------------------
    // Multiple Choice
    // -------------------------------------------------------------------------

    public function test_multiple_choice_scores_correct_career_from_weights(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'What do you like?',
            'type' => 'multiple_choice',
            'options' => ['Code', 'Paint', 'Teach'],
            'career_weights' => [
                'options' => [
                    ['Software Engineer' => 3, 'Data Scientist' => 1],
                    ['Artist' => 3],
                    ['Teacher' => 3],
                ],
            ],
            'answer' => 'Code',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Software Engineer', $result->career_title);
        $this->assertGreaterThan(0, $result->match_percentage);
    }

    public function test_multiple_choice_falls_back_to_keyword_scan_when_no_weights(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'What do you like?',
            'type' => 'multiple_choice',
            'options' => ['I love programming', 'I love painting'],
            'career_weights' => null,
            'answer' => 'I love programming',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Software Engineer', $result->career_title);
    }

    public function test_multiple_choice_answer_score_is_saved(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Pick one',
            'type' => 'multiple_choice',
            'options' => ['Code', 'Paint'],
            'career_weights' => [
                'options' => [
                    ['Software Engineer' => 3],
                    ['Artist' => 2],
                ],
            ],
            'answer' => 'Code',
        ]]);

        $this->service->generate($attempt);

        $answer = $attempt->answers()->first();
        $this->assertEquals(3, $answer->fresh()->score);
    }

    // -------------------------------------------------------------------------
    // Scale
    // -------------------------------------------------------------------------

    public function test_scale_applies_per_career_coefficients(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Rate your love for data',
            'type' => 'scale',
            'career_weights' => [
                'careers' => [
                    'Data Scientist' => 1.0,
                    'Software Engineer' => 0.0,
                    'Marketing Manager' => 0.0,
                    'Teacher' => 0.0,
                    'Doctor' => 0.0,
                    'Artist' => 0.0,
                ],
            ],
            'answer' => '8',
            'score' => 8,
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Data Scientist', $result->career_title);
        $this->assertArrayHasKey('Data Scientist', $result->detailed_analysis);
    }

    public function test_scale_fallback_gives_small_uniform_boost(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Rate something',
            'type' => 'scale',
            'career_weights' => null,
            'answer' => '10',
            'score' => 10,
        ]]);

        $result = $this->service->generate($attempt);

        // All careers should get equal boost → all equal percentages
        $analysis = $result->detailed_analysis;
        $values = array_values($analysis);
        $this->assertEquals(count(array_unique($values)), 1);
    }

    // -------------------------------------------------------------------------
    // Short Answer
    // -------------------------------------------------------------------------

    public function test_short_answer_uses_keyword_map_from_weights(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Describe yourself',
            'type' => 'short_answer',
            'career_weights' => [
                'keywords' => [
                    'hospital' => ['Doctor' => 3],
                    'code' => ['Software Engineer' => 3],
                ],
            ],
            'answer' => 'I work in a hospital every day',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Doctor', $result->career_title);
    }

    public function test_short_answer_falls_back_to_global_keyword_map(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'What do you do?',
            'type' => 'short_answer',
            'career_weights' => null,
            'answer' => 'I love teaching students in the classroom',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Teacher', $result->career_title);
    }

    public function test_short_answer_score_is_saved_based_on_keywords(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Describe yourself',
            'type' => 'short_answer',
            'career_weights' => [
                'keywords' => [
                    'coding' => ['Software Engineer' => 5],
                ],
            ],
            'answer' => 'I enjoy coding every day',
        ]]);

        $this->service->generate($attempt);

        $answer = $attempt->answers()->first();
        $this->assertEquals(5, $answer->fresh()->score);
    }

    // -------------------------------------------------------------------------
    // Negation detection
    // -------------------------------------------------------------------------

    public function test_short_answer_negation_reduces_score(): void
    {
        // Without negation — should match Software Engineer
        $attemptPositive = $this->makeAttempt([[
            'text' => 'Describe yourself',
            'type' => 'short_answer',
            'career_weights' => [
                'keywords' => [
                    'programming' => ['Software Engineer' => 3],
                    'teaching' => ['Teacher' => 3],
                ],
            ],
            'answer' => 'I love programming',
        ]]);
        $resultPositive = $this->service->generate($attemptPositive);

        // With negation — should NOT match Software Engineer strongly
        $attemptNegated = $this->makeAttempt([[
            'text' => 'Describe yourself',
            'type' => 'short_answer',
            'career_weights' => [
                'keywords' => [
                    'programming' => ['Software Engineer' => 3],
                    'teaching' => ['Teacher' => 3],
                ],
            ],
            'answer' => "I don't like programming but I love teaching",
        ]]);
        $resultNegated = $this->service->generate($attemptNegated);

        // The negated result should give Teacher higher than SE
        $this->assertEquals('Teacher', $resultNegated->career_title);
        // And the positive result should give SE highest
        $this->assertEquals('Software Engineer', $resultPositive->career_title);
    }

    public function test_negation_with_hate_keyword(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Tell us about yourself',
            'type' => 'short_answer',
            'career_weights' => [
                'keywords' => [
                    'coding' => ['Software Engineer' => 3],
                    'art' => ['Artist' => 3],
                ],
            ],
            'answer' => 'I hate coding but love art',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Artist', $result->career_title);
    }

    // -------------------------------------------------------------------------
    // Normalisation
    // -------------------------------------------------------------------------

    public function test_scale_and_mc_contribute_equally_after_normalisation(): void
    {
        // One MC question pointing to Artist, one scale question pointing to SE
        // With normalisation, they should contribute on the same scale
        $attempt = $this->makeAttempt([
            [
                'text' => 'Q1',
                'type' => 'multiple_choice',
                'options' => ['Paint', 'Code'],
                'career_weights' => ['options' => [['Artist' => 3], ['Software Engineer' => 3]]],
                'answer' => 'Paint',
                'importance' => 1.0,
            ],
            [
                'text' => 'Q2',
                'type' => 'scale',
                'career_weights' => ['careers' => [
                    'Software Engineer' => 0.9,
                    'Data Scientist' => 0.0, 'Marketing Manager' => 0.0,
                    'Teacher' => 0.0, 'Doctor' => 0.0, 'Artist' => 0.0,
                ]],
                'answer' => '10',
                'score' => 10,
                'importance' => 1.0,
            ],
        ]);

        $result = $this->service->generate($attempt);

        // Both questions have importance 1.0, and both give max score for their career
        // So SE and Artist should have similar scores (both normalised to 1.0)
        $analysis = $result->detailed_analysis;
        $seDiff = abs($analysis['Software Engineer'] - $analysis['Artist']);
        // They should be very close (within 1% difference)
        $this->assertLessThan(1.0, $seDiff, 'MC and scale should contribute equally after normalisation');
    }

    // -------------------------------------------------------------------------
    // Question importance
    // -------------------------------------------------------------------------

    public function test_higher_importance_question_has_more_impact(): void
    {
        $attempt = $this->makeAttempt([
            [
                'text' => 'Low importance Q',
                'type' => 'multiple_choice',
                'options' => ['Code'],
                'career_weights' => ['options' => [['Software Engineer' => 3]]],
                'answer' => 'Code',
                'importance' => 0.5,
            ],
            [
                'text' => 'High importance Q',
                'type' => 'multiple_choice',
                'options' => ['Paint'],
                'career_weights' => ['options' => [['Artist' => 3]]],
                'answer' => 'Paint',
                'importance' => 2.0,
            ],
        ]);

        $result = $this->service->generate($attempt);

        // Artist should win because its question has 4x importance
        $this->assertEquals('Artist', $result->career_title);
        $this->assertGreaterThan(
            $result->detailed_analysis['Software Engineer'],
            $result->detailed_analysis['Artist']
        );
    }

    // -------------------------------------------------------------------------
    // Category scores
    // -------------------------------------------------------------------------

    public function test_category_scores_are_included_in_result(): void
    {
        $attempt = $this->makeAttempt([
            [
                'text' => 'Q1',
                'type' => 'multiple_choice',
                'options' => ['Code'],
                'career_weights' => ['options' => [['Software Engineer' => 3]]],
                'answer' => 'Code',
                'category' => 'skills',
            ],
            [
                'text' => 'Q2',
                'type' => 'multiple_choice',
                'options' => ['Paint'],
                'career_weights' => ['options' => [['Artist' => 3]]],
                'answer' => 'Paint',
                'category' => 'interests',
            ],
        ]);

        $result = $this->service->generate($attempt);

        $this->assertNotNull($result->category_scores);
        $this->assertArrayHasKey('skills', $result->category_scores);
        $this->assertArrayHasKey('interests', $result->category_scores);
    }

    // -------------------------------------------------------------------------
    // Result generation
    // -------------------------------------------------------------------------

    public function test_top_career_is_highest_scoring(): void
    {
        $attempt = $this->makeAttempt([
            [
                'text' => 'Q1',
                'type' => 'multiple_choice',
                'options' => ['Paint', 'Code'],
                'career_weights' => ['options' => [['Artist' => 3], ['Software Engineer' => 3]]],
                'answer' => 'Paint',
            ],
            [
                'text' => 'Q2',
                'type' => 'multiple_choice',
                'options' => ['Draw', 'Analyze'],
                'career_weights' => ['options' => [['Artist' => 3], ['Data Scientist' => 3]]],
                'answer' => 'Draw',
            ],
        ]);

        $result = $this->service->generate($attempt);

        $this->assertEquals('Artist', $result->career_title);
    }

    public function test_match_percentage_is_capped_at_100(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Q',
            'type' => 'scale',
            'career_weights' => ['careers' => [
                'Software Engineer' => 100.0,
                'Data Scientist' => 0.0, 'Marketing Manager' => 0.0,
                'Teacher' => 0.0, 'Doctor' => 0.0, 'Artist' => 0.0,
            ]],
            'answer' => '10',
            'score' => 10,
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertLessThanOrEqual(100, $result->match_percentage);
    }

    public function test_career_result_is_persisted_to_database(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Q',
            'type' => 'multiple_choice',
            'options' => ['Code'],
            'career_weights' => ['options' => [['Software Engineer' => 3]]],
            'answer' => 'Code',
        ]]);

        $this->service->generate($attempt);

        $this->assertDatabaseHas('career_results', [
            'test_attempt_id' => $attempt->id,
        ]);
    }

    public function test_career_result_includes_skills_and_paths(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Q',
            'type' => 'multiple_choice',
            'options' => ['Code'],
            'career_weights' => ['options' => [['Software Engineer' => 3]]],
            'answer' => 'Code',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertIsArray($result->career_skills);
        $this->assertIsArray($result->career_paths);
        $this->assertNotEmpty($result->career_skills);
        $this->assertNotEmpty($result->career_paths);
    }

    public function test_career_id_is_set_on_result(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Q',
            'type' => 'multiple_choice',
            'options' => ['Code'],
            'career_weights' => ['options' => [['Software Engineer' => 3]]],
            'answer' => 'Code',
        ]]);

        $result = $this->service->generate($attempt);

        $this->assertNotNull($result->career_id);
        $career = Career::find($result->career_id);
        $this->assertEquals('Software Engineer', $career->title);
    }

    // -------------------------------------------------------------------------
    // Metadata helpers
    // -------------------------------------------------------------------------

    public function test_get_description_returns_correct_text_for_known_career(): void
    {
        $desc = $this->service->getDescription('Teacher');
        $this->assertStringContainsString('educate', strtolower($desc));
    }

    public function test_get_description_returns_default_for_unknown_career(): void
    {
        $desc = $this->service->getDescription('Astronaut');
        $this->assertStringContainsString('career', strtolower($desc));
    }

    public function test_get_skills_returns_non_empty_array(): void
    {
        $careers = Career::where('is_active', true)->pluck('title');
        foreach ($careers as $career) {
            $skills = $this->service->getSkills($career);
            $this->assertIsArray($skills);
            $this->assertNotEmpty($skills);
        }
    }

    public function test_get_paths_returns_non_empty_array(): void
    {
        $careers = Career::where('is_active', true)->pluck('title');
        foreach ($careers as $career) {
            $paths = $this->service->getPaths($career);
            $this->assertIsArray($paths);
            $this->assertNotEmpty($paths);
        }
    }

    // -------------------------------------------------------------------------
    // Detailed analysis contains percentages
    // -------------------------------------------------------------------------

    public function test_detailed_analysis_contains_percentages(): void
    {
        $attempt = $this->makeAttempt([[
            'text' => 'Q',
            'type' => 'multiple_choice',
            'options' => ['Code'],
            'career_weights' => ['options' => [['Software Engineer' => 3]]],
            'answer' => 'Code',
        ]]);

        $result = $this->service->generate($attempt);

        foreach ($result->detailed_analysis as $career => $value) {
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertLessThanOrEqual(100, $value);
        }
    }
}

