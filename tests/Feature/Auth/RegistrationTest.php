<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_are_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'secret_word' => 'mysecret',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_registration_stores_hashed_secret_word(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'secret_word' => 'mysecret',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('mysecret', $user->secret_word));
    }

    public function test_registration_assigns_student_role(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'secret_word' => 'mysecret',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'student',
        ]);
    }

    public function test_authenticated_user_cannot_access_register_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/register');
        $response->assertRedirect();
    }

    public function test_registration_requires_name(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'secret_word' => 'mysecret',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'secret_word' => 'mysecret',
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
            'secret_word' => 'mysecret',
        ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_registration_requires_secret_word(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors('secret_word');
    }
}
