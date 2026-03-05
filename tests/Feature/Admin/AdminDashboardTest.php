<?php

namespace Tests\Feature\Admin;

use App\Models\CareerTest;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_admin_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_dashboard_shows_total_tests_count(): void
    {
        $admin = $this->admin();
        CareerTest::create(['title' => 'T1', 'description' => 'D', 'duration_minutes' => 10, 'is_active' => true]);
        CareerTest::create(['title' => 'T2', 'description' => 'D', 'duration_minutes' => 10, 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertSee('2');
    }

    public function test_admin_dashboard_shows_total_attempts_count(): void
    {
        $admin = $this->admin();
        $student = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
        $test = CareerTest::create(['title' => 'T', 'description' => 'D', 'duration_minutes' => 10, 'is_active' => true]);

        TestAttempt::create([
            'user_id' => $student->id,
            'career_test_id' => $test->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        // Page renders with attempt data available
        $response->assertSee('1');
    }

    public function test_admin_dashboard_shows_recent_tests(): void
    {
        $admin = $this->admin();
        $test = CareerTest::create([
            'title' => 'Most Recent Test', 'description' => 'D',
            'duration_minutes' => 10, 'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertSee('Most Recent Test');
    }
}

