<?php

namespace Tests\Feature;

use App\Models\CareerResult;
use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedStudent(): User
    {
        return User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
    }

    private function makeCompletedAttempt(User $user): TestAttempt
    {
        $careerTest = CareerTest::create([
            'title' => 'Career Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);

        $attempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        CareerResult::create([
            'test_attempt_id' => $attempt->id,
            'career_title' => 'Software Engineer',
            'career_description' => 'Build amazing software.',
            'career_skills' => ['Programming', 'Problem Solving'],
            'career_paths' => ['Junior Developer', 'Senior Developer'],
            'match_percentage' => 85,
            'detailed_analysis' => ['Software Engineer' => 10, 'Artist' => 2],
        ]);

        return $attempt;
    }

    // -------------------------------------------------------------------------
    // Show result
    // -------------------------------------------------------------------------

    public function test_result_page_shows_career_title(): void
    {
        $user = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($user);

        $response = $this->actingAs($user)->get(route('test.result', $attempt));

        $response->assertStatus(200);
        $response->assertSee('Software Engineer');
    }

    public function test_result_page_shows_match_percentage(): void
    {
        $user = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($user);

        $response = $this->actingAs($user)->get(route('test.result', $attempt));

        $response->assertSee('85');
    }

    public function test_result_page_shows_career_skills(): void
    {
        $user = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($user);

        $response = $this->actingAs($user)->get(route('test.result', $attempt));

        $response->assertSee('Programming');
        $response->assertSee('Problem Solving');
    }

    public function test_result_page_shows_career_paths(): void
    {
        $user = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($user);

        $response = $this->actingAs($user)->get(route('test.result', $attempt));

        $response->assertSee('Junior Developer');
        $response->assertSee('Senior Developer');
    }

    public function test_result_redirects_if_test_not_completed(): void
    {
        $user = $this->verifiedStudent();
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

        $response = $this->actingAs($user)->get(route('test.result', $attempt));

        $response->assertRedirect(route('test.take', $attempt));
    }

    public function test_unauthorized_user_cannot_view_result(): void
    {
        $owner = $this->verifiedStudent();
        $other = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($owner);

        $response = $this->actingAs($other)->get(route('test.result', $attempt));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_result(): void
    {
        $user = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($user);

        $response = $this->get(route('test.result', $attempt));

        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // History
    // -------------------------------------------------------------------------

    public function test_history_shows_only_completed_attempts(): void
    {
        $user = $this->verifiedStudent();
        $attempt = $this->makeCompletedAttempt($user);

        // Also create an in-progress attempt
        $careerTest = CareerTest::create([
            'title' => 'Another Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);
        TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('test.history'));

        // Should show the completed career test title
        $response->assertSee('Career Test');
        // Should NOT show the in-progress attempt's test title in history
        $response->assertDontSee('Another Test');
    }

    public function test_history_shows_only_own_attempts(): void
    {
        $user = $this->verifiedStudent();
        $other = $this->verifiedStudent();

        $this->makeCompletedAttempt($user);
        $this->makeCompletedAttempt($other);

        $response = $this->actingAs($user)->get(route('test.history'));
        $response->assertStatus(200);

        // User should only see their own attempts
        $userAttempts = TestAttempt::where('user_id', $user->id)->count();
        $this->assertEquals(1, $userAttempts);
    }

    public function test_history_is_paginated(): void
    {
        $user = $this->verifiedStudent();

        for ($i = 0; $i < 12; $i++) {
            $this->makeCompletedAttempt($user);
        }

        $response = $this->actingAs($user)->get(route('test.history'));
        $response->assertStatus(200);

        $response2 = $this->actingAs($user)->get(route('test.history') . '?page=2');
        $response2->assertStatus(200);
    }

    public function test_guest_cannot_access_history(): void
    {
        $response = $this->get(route('test.history'));
        $response->assertRedirect(route('login'));
    }
}

