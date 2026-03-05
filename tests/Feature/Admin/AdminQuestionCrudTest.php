<?php

namespace Tests\Feature\Admin;

use App\Models\CareerTest;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuestionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    private function makeTest(): CareerTest
    {
        return CareerTest::create([
            'title' => 'Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // Create question
    // -------------------------------------------------------------------------

    public function test_admin_can_view_create_question_form(): void
    {
        $test = $this->makeTest();
        $response = $this->actingAs($this->admin())
            ->get(route('admin.questions.create', $test));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_multiple_choice_question(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();

        $response = $this->actingAs($admin)->post(
            route('admin.questions.store', $test),
            [
                'question_text' => 'What do you enjoy?',
                'question_type' => 'multiple_choice',
                'options' => ['Coding', 'Painting', 'Teaching'],
                'order' => 1,
            ]
        );

        $this->assertDatabaseHas('questions', [
            'career_test_id' => $test->id,
            'question_text' => 'What do you enjoy?',
            'question_type' => 'multiple_choice',
        ]);
        $response->assertRedirect(route('admin.tests.questions', $test));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_create_scale_question(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();

        $this->actingAs($admin)->post(
            route('admin.questions.store', $test),
            [
                'question_text' => 'Rate your love for data (1-10)',
                'question_type' => 'scale',
                'order' => 2,
            ]
        );

        $this->assertDatabaseHas('questions', [
            'career_test_id' => $test->id,
            'question_type' => 'scale',
        ]);
    }

    public function test_admin_can_create_short_answer_question(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();

        $this->actingAs($admin)->post(
            route('admin.questions.store', $test),
            [
                'question_text' => 'Describe your ideal job.',
                'question_type' => 'short_answer',
                'order' => 3,
            ]
        );

        $this->assertDatabaseHas('questions', [
            'question_type' => 'short_answer',
            'question_text' => 'Describe your ideal job.',
        ]);
    }

    public function test_multiple_choice_question_requires_options(): void
    {
        $test = $this->makeTest();

        $response = $this->actingAs($this->admin())->post(
            route('admin.questions.store', $test),
            [
                'question_text' => 'Pick one',
                'question_type' => 'multiple_choice',
                // no options provided
                'order' => 1,
            ]
        );

        $response->assertSessionHasErrors('options');
    }

    public function test_question_type_must_be_valid(): void
    {
        $test = $this->makeTest();

        $response = $this->actingAs($this->admin())->post(
            route('admin.questions.store', $test),
            [
                'question_text' => 'Q',
                'question_type' => 'invalid_type',
                'order' => 1,
            ]
        );

        $response->assertSessionHasErrors('question_type');
    }

    public function test_create_question_requires_question_text(): void
    {
        $test = $this->makeTest();

        $response = $this->actingAs($this->admin())->post(
            route('admin.questions.store', $test),
            [
                'question_type' => 'scale',
                'order' => 1,
            ]
        );

        $response->assertSessionHasErrors('question_text');
    }

    // -------------------------------------------------------------------------
    // Edit / Update question
    // -------------------------------------------------------------------------

    public function test_admin_can_view_edit_question_form(): void
    {
        $test = $this->makeTest();
        $question = Question::create([
            'career_test_id' => $test->id,
            'question_text' => 'Original question?',
            'question_type' => 'scale',
            'order' => 1,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.questions.edit', $question));

        $response->assertStatus(200);
        $response->assertSee('Original question?');
    }

    public function test_admin_can_update_a_question(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();
        $question = Question::create([
            'career_test_id' => $test->id,
            'question_text' => 'Old text',
            'question_type' => 'scale',
            'order' => 1,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.questions.update', $question),
            [
                'question_text' => 'Updated text',
                'question_type' => 'scale',
                'order' => 1,
            ]
        );

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'question_text' => 'Updated text',
        ]);
        $response->assertRedirect(route('admin.tests.questions', $test));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // Delete question
    // -------------------------------------------------------------------------

    public function test_admin_can_delete_a_question(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();
        $question = Question::create([
            'career_test_id' => $test->id,
            'question_text' => 'Delete me',
            'question_type' => 'scale',
            'order' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.questions.destroy', $question));

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $response->assertRedirect(route('admin.tests.questions', $test));
        $response->assertSessionHas('success');
    }
}

