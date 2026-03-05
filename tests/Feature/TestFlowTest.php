<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\CareerResult;
use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestFlowTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedStudent(): User
    {
        return User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
    }

    private function makeTestWithQuestions(int $count = 3): CareerTest
    {
        $careerTest = CareerTest::create([
            'title' => 'Sample Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);

        for ($i = 1; $i <= $count; $i++) {
            Question::create([
                'career_test_id' => $careerTest->id,
                'question_text' => "Question {$i}",
                'question_type' => 'multiple_choice',
                'options' => ['Option A', 'Option B'],
                'career_weights' => [
                    'options' => [
                        ['Software Engineer' => 3],
                        ['Artist' => 3],
                    ],
                ],
                'order' => $i,
            ]);
        }

        return $careerTest;
    }

    // -------------------------------------------------------------------------
    // Start test
    // -------------------------------------------------------------------------

    public function test_student_can_start_a_test(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();

        $response = $this->actingAs($user)
            ->post(route('test.start', $careerTest));

        $this->assertDatabaseHas('test_attempts', [
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'status' => 'in_progress',
        ]);
        $response->assertRedirect();
    }

    public function test_starting_test_again_resumes_existing_in_progress_attempt(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();

        // Start once
        $this->actingAs($user)->post(route('test.start', $careerTest));

        // Start again
        $this->actingAs($user)->post(route('test.start', $careerTest));

        // Should only have one attempt
        $this->assertEquals(1, TestAttempt::where('user_id', $user->id)->count());
    }

    public function test_guest_cannot_start_a_test(): void
    {
        $careerTest = $this->makeTestWithQuestions();
        $response = $this->post(route('test.start', $careerTest));
        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Take test (display question)
    // -------------------------------------------------------------------------

    public function test_take_shows_first_question(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('test.take', $attempt));

        $response->assertStatus(200);
        $response->assertSee('Question 1');
    }

    public function test_take_shows_correct_progress(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions(3);

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Answer first question
        $q1 = $careerTest->questions->first();
        Answer::create([
            'test_attempt_id' => $attempt->id,
            'question_id' => $q1->id,
            'answer_text' => 'Option A',
        ]);

        $response = $this->actingAs($user)->get(route('test.take', $attempt));
        $response->assertSee('Question 2');
    }

    public function test_take_redirects_completed_attempt_to_result(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'completed_at' => now(),
            'status' => 'completed',
        ]);
        CareerResult::create([
            'test_attempt_id' => $attempt->id,
            'career_title' => 'Software Engineer',
            'career_description' => 'Desc',
            'career_skills' => ['Coding'],
            'career_paths' => ['Junior'],
            'match_percentage' => 80,
            'detailed_analysis' => [],
        ]);

        $response = $this->actingAs($user)->get(route('test.take', $attempt));
        $response->assertRedirect(route('test.result', $attempt));
    }

    public function test_take_another_users_attempt_returns_403(): void
    {
        $user = $this->verifiedStudent();
        $other = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();

        $attempt = TestAttempt::create([
            'user_id' => $other->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('test.take', $attempt));
        $response->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Submit answer
    // -------------------------------------------------------------------------

    public function test_submit_answer_saves_answer_to_database(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();
        $question = $careerTest->questions->first();

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)->post(route('test.submit', $attempt), [
            'question_id' => $question->id,
            'answer_text' => 'Option A',
        ]);

        $this->assertDatabaseHas('answers', [
            'test_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'answer_text' => 'Option A',
        ]);
    }

    public function test_submit_answer_advances_to_next_question(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions(3);
        $question = $careerTest->questions->first();

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->post(route('test.submit', $attempt), [
            'question_id' => $question->id,
            'answer_text' => 'Option A',
        ]);

        $response->assertRedirect(route('test.take', $attempt));
    }

    public function test_submit_to_another_users_attempt_returns_403(): void
    {
        $user = $this->verifiedStudent();
        $other = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();
        $question = $careerTest->questions->first();

        $attempt = TestAttempt::create([
            'user_id' => $other->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->post(route('test.submit', $attempt), [
            'question_id' => $question->id,
            'answer_text' => 'Option A',
        ]);

        $response->assertForbidden();
    }

    public function test_submit_requires_question_id_and_answer(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->post(route('test.submit', $attempt), []);
        $response->assertSessionHasErrors(['question_id', 'answer_text']);
    }

    // -------------------------------------------------------------------------
    // Test completion
    // -------------------------------------------------------------------------

    public function test_test_completes_when_all_questions_are_answered(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions(2);
        $questions = $careerTest->questions;

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Submit all answers, then follow the final redirect to test.take
        // which triggers completeTest() when all questions are answered
        foreach ($questions as $question) {
            $this->actingAs($user)->post(route('test.submit', $attempt), [
                'question_id' => $question->id,
                'answer_text' => 'Option A',
            ]);
        }

        // After all answers are submitted, visiting test.take triggers completion
        $this->actingAs($user)->get(route('test.take', $attempt));

        $this->assertDatabaseHas('test_attempts', [
            'id' => $attempt->id,
            'status' => 'completed',
        ]);
    }

    public function test_completing_test_creates_career_result(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions(2);
        $questions = $careerTest->questions;

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        foreach ($questions as $question) {
            $this->actingAs($user)->post(route('test.submit', $attempt), [
                'question_id' => $question->id,
                'answer_text' => 'Option A',
            ]);
        }

        // Visiting take after all answers triggers completeTest()
        $this->actingAs($user)->get(route('test.take', $attempt));

        $this->assertDatabaseHas('career_results', [
            'test_attempt_id' => $attempt->id,
        ]);
    }

    public function test_submitting_to_completed_test_redirects_to_result(): void
    {
        $user = $this->verifiedStudent();
        $careerTest = $this->makeTestWithQuestions();
        $question = $careerTest->questions->first();

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'completed_at' => now(),
            'status' => 'completed',
        ]);
        CareerResult::create([
            'test_attempt_id' => $attempt->id,
            'career_title' => 'Software Engineer',
            'career_description' => 'Desc',
            'career_skills' => ['Coding'],
            'career_paths' => ['Junior'],
            'match_percentage' => 80,
            'detailed_analysis' => [],
        ]);

        $response = $this->actingAs($user)->post(route('test.submit', $attempt), [
            'question_id' => $question->id,
            'answer_text' => 'Option A',
        ]);

        $response->assertRedirect(route('test.result', $attempt));
    }
}

