<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecretWord2FATest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role = 'student'): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => Hash::make('password'),
            'secret_word' => Hash::make('mysecret'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Login step 1: email + password → redirects to secret word form
    // -------------------------------------------------------------------------

    public function test_login_with_correct_credentials_redirects_to_secret_word(): void
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('secret-word.verify'));
        $this->assertGuest(); // Not logged in yet
        $this->assertEquals($user->id, session('2fa_user_id'));
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_with_nonexistent_email_fails(): void
    {
        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Login step 2: secret word verification
    // -------------------------------------------------------------------------

    public function test_secret_word_form_renders_when_session_has_2fa_user(): void
    {
        $user = $this->createUser();

        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->get(route('secret-word.verify'));

        $response->assertStatus(200);
    }

    public function test_secret_word_form_redirects_to_login_without_session(): void
    {
        $response = $this->get(route('secret-word.verify'));

        $response->assertRedirect(route('login'));
    }

    public function test_correct_secret_word_completes_login(): void
    {
        $user = $this->createUser();

        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('secret-word.check'), [
                'secret_word' => 'mysecret',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_secret_word_fails(): void
    {
        $user = $this->createUser();

        $response = $this->withSession(['2fa_user_id' => $user->id])
            ->post(route('secret-word.check'), [
                'secret_word' => 'wrongsecret',
            ]);

        $response->assertSessionHasErrors('secret_word');
        $this->assertGuest();
    }

    public function test_secret_word_check_without_session_redirects_to_login(): void
    {
        $response = $this->post(route('secret-word.check'), [
            'secret_word' => 'anything',
        ]);

        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Full 2FA flow
    // -------------------------------------------------------------------------

    public function test_full_2fa_login_flow(): void
    {
        $user = $this->createUser();

        // Step 1: credentials
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('secret-word.verify'));
        $this->assertGuest();

        // Step 2: secret word
        $response = $this->post(route('secret-word.check'), [
            'secret_word' => 'mysecret',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    // -------------------------------------------------------------------------
    // Registration with secret word
    // -------------------------------------------------------------------------

    public function test_registration_requires_secret_word(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // no secret_word
        ]);

        $response->assertSessionHasErrors('secret_word');
    }

    public function test_registration_with_secret_word_succeeds(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'secret_word' => 'mytopsecret',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('mytopsecret', $user->secret_word));
    }

    // -------------------------------------------------------------------------
    // Authenticated routes are accessible (no email verification needed)
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_profile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}

