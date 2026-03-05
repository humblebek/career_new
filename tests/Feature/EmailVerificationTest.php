<?php

namespace Tests\Feature;

use App\Models\CareerTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function unverifiedUser(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'email_verified_at' => null,
        ]);
    }

    public function test_verification_notice_page_renders_for_unverified_user(): void
    {
        $user = $this->unverifiedUser();
        $response = $this->actingAs($user)->get('/verify-email');
        $response->assertStatus(200);
    }

    public function test_verified_user_is_redirected_from_verification_notice(): void
    {
        $user = $this->verifiedUser();
        $response = $this->actingAs($user)->get('/verify-email');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_unverified_user_is_redirected_from_dashboard(): void
    {
        $user = $this->unverifiedUser();
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_unverified_user_is_redirected_from_test_routes(): void
    {
        $user = $this->unverifiedUser();
        $careerTest = CareerTest::create([
            'title' => 'Test', 'description' => 'Desc',
            'duration_minutes' => 10, 'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('test.start', $careerTest));
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_unverified_user_is_redirected_from_profile(): void
    {
        $user = $this->unverifiedUser();
        $response = $this->actingAs($user)->get('/profile');
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_dashboard(): void
    {
        $user = $this->verifiedUser();
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login_from_verification_notice(): void
    {
        $response = $this->get('/verify-email');
        $response->assertRedirect(route('login'));
    }

    public function test_resend_verification_email_sends_notification(): void
    {
        Notification::fake();

        $user = $this->unverifiedUser();

        $response = $this->actingAs($user)
            ->post(route('verification.send'));

        Notification::assertSentTo($user, VerifyEmail::class);
        $response->assertRedirect();
    }

    public function test_resend_verification_sets_session_status(): void
    {
        Notification::fake();

        $user = $this->unverifiedUser();

        $response = $this->actingAs($user)
            ->post(route('verification.send'));

        $response->assertSessionHas('status', 'verification-link-sent');
    }
}

