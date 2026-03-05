<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuestion(array $attrs = []): Question
    {
        $test = CareerTest::create([
            'title' => 'Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);

        return Question::create(array_merge([
            'career_test_id' => $test->id,
            'question_text' => 'Sample question?',
            'question_type' => 'scale',
            'order' => 1,
        ], $attrs));
    }

    public function test_options_are_cast_to_array(): void
    {
        $q = $this->makeQuestion([
            'question_type' => 'multiple_choice',
            'options' => ['Option A', 'Option B'],
        ]);

        $this->assertIsArray($q->fresh()->options);
        $this->assertContains('Option A', $q->fresh()->options);
    }

    public function test_career_weights_are_cast_to_array(): void
    {
        $weights = ['options' => [['Software Engineer' => 3]]];
        $q = $this->makeQuestion([
            'question_type' => 'multiple_choice',
            'options' => ['Code'],
            'career_weights' => $weights,
        ]);

        $this->assertIsArray($q->fresh()->career_weights);
        $this->assertArrayHasKey('options', $q->fresh()->career_weights);
    }

    public function test_belongs_to_career_test(): void
    {
        $q = $this->makeQuestion();
        $this->assertInstanceOf(CareerTest::class, $q->careerTest);
    }

    public function test_has_many_answers(): void
    {
        $q = $this->makeQuestion();
        $user = User::factory()->create();
        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $q->career_test_id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);
        Answer::create([
            'test_attempt_id' => $attempt->id,
            'question_id' => $q->id,
            'answer_text' => 'My answer',
        ]);

        $this->assertCount(1, $q->answers);
    }

    public function test_null_options_stays_null(): void
    {
        $q = $this->makeQuestion(['question_type' => 'scale', 'options' => null]);
        $this->assertNull($q->fresh()->options);
    }

    public function test_null_career_weights_stays_null(): void
    {
        $q = $this->makeQuestion(['career_weights' => null]);
        $this->assertNull($q->fresh()->career_weights);
    }
}

