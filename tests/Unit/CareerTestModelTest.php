<?php

namespace Tests\Unit;

use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerTestModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeTest(array $attrs = []): CareerTest
    {
        return CareerTest::create(array_merge([
            'title' => 'Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ], $attrs));
    }

    public function test_has_many_questions(): void
    {
        $test = $this->makeTest();
        Question::create([
            'career_test_id' => $test->id,
            'question_text' => 'Q1',
            'question_type' => 'scale',
            'order' => 1,
        ]);

        $this->assertCount(1, $test->questions);
    }

    public function test_has_many_test_attempts(): void
    {
        $test = $this->makeTest();
        $user = User::factory()->create();
        TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $test->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->assertCount(1, $test->testAttempts);
    }

    public function test_questions_are_ordered_by_order_column(): void
    {
        $test = $this->makeTest();
        Question::create(['career_test_id' => $test->id, 'question_text' => 'Q3', 'question_type' => 'scale', 'order' => 3]);
        Question::create(['career_test_id' => $test->id, 'question_text' => 'Q1', 'question_type' => 'scale', 'order' => 1]);
        Question::create(['career_test_id' => $test->id, 'question_text' => 'Q2', 'question_type' => 'scale', 'order' => 2]);

        $orders = $test->questions->pluck('order')->toArray();
        $this->assertEquals([1, 2, 3], $orders);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $test = $this->makeTest(['is_active' => true]);
        $this->assertIsBool($test->is_active);
        $this->assertTrue($test->is_active);
    }

    public function test_inactive_test_can_be_created(): void
    {
        $test = $this->makeTest(['is_active' => false]);
        $this->assertFalse($test->is_active);
    }
}

