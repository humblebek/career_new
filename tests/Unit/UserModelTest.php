<?php

namespace Tests\Unit;

use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_student_role(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_student_returns_true_for_student_role(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->assertTrue($user->isStudent());
    }

    public function test_is_student_returns_false_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertFalse($user->isStudent());
    }

    public function test_user_has_many_test_attempts(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $careerTest = CareerTest::create([
            'title' => 'Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);
        TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->assertCount(1, $user->testAttempts);
    }

    public function test_user_fillable_includes_role(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $this->assertEquals('admin', $user->role);
    }

    public function test_password_is_hidden_in_serialization(): void
    {
        $user = User::factory()->make();
        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }
}

