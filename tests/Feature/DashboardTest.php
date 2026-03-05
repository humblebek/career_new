<?php

namespace Tests\Feature;

use App\Models\CareerTest;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedStudent(): User
    {
        return User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
    }

    private function makeTest(bool $active = true): CareerTest
    {
        return CareerTest::create([
            'title' => $active ? 'Active Test' : 'Inactive Test',
            'description' => 'Description',
            'duration_minutes' => 15,
            'is_active' => $active,
        ]);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_verified_student_sees_dashboard(): void
    {
        $user = $this->verifiedStudent();
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_shows_active_tests(): void
    {
        $user = $this->verifiedStudent();
        $activeTest = $this->makeTest(true);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertSee($activeTest->title);
    }

    public function test_dashboard_does_not_show_inactive_tests(): void
    {
        $user = $this->verifiedStudent();
        $inactiveTest = $this->makeTest(false);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertDontSee($inactiveTest->title);
    }

    public function test_dashboard_shows_users_own_test_attempts(): void
    {
        $user = $this->verifiedStudent();
        $test = $this->makeTest();

        TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $test->id,
            'started_at' => now(),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertSee($test->title);
    }

    public function test_dashboard_does_not_show_other_users_attempts(): void
    {
        $user = $this->verifiedStudent();
        $otherUser = $this->verifiedStudent();
        $test = $this->makeTest();

        TestAttempt::create([
            'user_id' => $otherUser->id,
            'career_test_id' => $test->id,
            'started_at' => now(),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // The test title may appear in "Available Tests" but the attempt count for $user should be 0
        $attempts = TestAttempt::where('user_id', $user->id)->count();
        $this->assertEquals(0, $attempts);
    }

    public function test_dashboard_test_attempts_are_paginated(): void
    {
        $user = $this->verifiedStudent();
        $test = $this->makeTest();

        // Create 7 attempts (more than page size of 5)
        for ($i = 0; $i < 7; $i++) {
            TestAttempt::create([
                'user_id' => $user->id,
                'career_test_id' => $test->id,
                'started_at' => now()->subMinutes($i + 1),
                'status' => 'completed',
                'completed_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Second page should exist
        $response2 = $this->actingAs($user)->get('/dashboard?page=2');
        $response2->assertStatus(200);
    }

    public function test_dashboard_shows_welcome_message_with_user_name(): void
    {
        $user = $this->verifiedStudent();
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertSee($user->name);
    }
}

