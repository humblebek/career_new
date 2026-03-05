<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    public function test_student_gets_403_on_admin_dashboard(): void
    {
        $response = $this->actingAs($this->student())->get('/admin');
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin');
        $response->assertStatus(200);
    }

    public function test_student_gets_403_on_admin_tests_list(): void
    {
        $response = $this->actingAs($this->student())->get('/admin/tests');
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_tests_list(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/tests');
        $response->assertStatus(200);
    }

    public function test_student_gets_403_on_admin_test_create(): void
    {
        $response = $this->actingAs($this->student())->get('/admin/tests/create');
        $response->assertForbidden();
    }

    public function test_student_cannot_post_to_admin_tests(): void
    {
        $response = $this->actingAs($this->student())->post('/admin/tests', [
            'title' => 'Test', 'description' => 'Desc', 'duration_minutes' => 10,
        ]);
        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_tests(): void
    {
        $response = $this->get('/admin/tests');
        $response->assertRedirect(route('login'));
    }
}

