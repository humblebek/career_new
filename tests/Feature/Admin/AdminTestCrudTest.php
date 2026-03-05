<?php

namespace Tests\Feature\Admin;

use App\Models\CareerTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTestCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    private function makeTest(array $attrs = []): CareerTest
    {
        return CareerTest::create(array_merge([
            'title' => 'Test Title', 'description' => 'Description',
            'duration_minutes' => 20, 'is_active' => true,
        ], $attrs));
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_admin_can_view_tests_list(): void
    {
        $admin = $this->admin();
        $this->makeTest(['title' => 'Visible Test']);

        $response = $this->actingAs($admin)->get('/admin/tests');

        $response->assertStatus(200);
        $response->assertSee('Visible Test');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_admin_can_view_create_test_form(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/tests/create');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_a_career_test(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/tests', [
            'title' => 'New Career Test',
            'description' => 'A new test description',
            'duration_minutes' => 25,
        ]);

        $this->assertDatabaseHas('career_tests', [
            'title' => 'New Career Test',
            'duration_minutes' => 25,
        ]);
        $response->assertRedirect(route('admin.tests'));
        $response->assertSessionHas('success');
    }

    public function test_create_test_requires_title(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/tests', [
            'description' => 'No title test',
            'duration_minutes' => 10,
        ]);
        $response->assertSessionHasErrors('title');
    }

    public function test_create_test_requires_description(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/tests', [
            'title' => 'No desc',
            'duration_minutes' => 10,
        ]);
        $response->assertSessionHasErrors('description');
    }

    public function test_create_test_requires_valid_duration(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/tests', [
            'title' => 'Test',
            'description' => 'Desc',
            'duration_minutes' => 0,  // min is 1
        ]);
        $response->assertSessionHasErrors('duration_minutes');
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function test_admin_can_view_edit_test_form(): void
    {
        $test = $this->makeTest();
        $response = $this->actingAs($this->admin())->get("/admin/tests/{$test->id}/edit");
        $response->assertStatus(200);
        $response->assertSee($test->title);
    }

    public function test_admin_can_update_a_career_test(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();

        $response = $this->actingAs($admin)->put("/admin/tests/{$test->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('career_tests', [
            'id' => $test->id,
            'title' => 'Updated Title',
            'duration_minutes' => 45,
        ]);
        $response->assertRedirect(route('admin.tests'));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_toggle_test_active_status(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest(['is_active' => true]);

        $this->actingAs($admin)->put("/admin/tests/{$test->id}", [
            'title' => $test->title,
            'description' => $test->description,
            'duration_minutes' => $test->duration_minutes,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('career_tests', [
            'id' => $test->id,
            'is_active' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_admin_can_delete_a_career_test(): void
    {
        $admin = $this->admin();
        $test = $this->makeTest();

        $response = $this->actingAs($admin)->delete("/admin/tests/{$test->id}");

        $this->assertDatabaseMissing('career_tests', ['id' => $test->id]);
        $response->assertRedirect(route('admin.tests'));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // Questions page
    // -------------------------------------------------------------------------

    public function test_admin_can_view_test_questions_page(): void
    {
        $test = $this->makeTest();
        $response = $this->actingAs($this->admin())
            ->get("/admin/tests/{$test->id}/questions");
        $response->assertStatus(200);
    }
}

