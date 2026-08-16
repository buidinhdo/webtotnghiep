<?php

namespace Tests\Feature\Auth;

use App\Mail\LoginOtpMail;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_admin_can_authenticate_directly_without_otp(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_regular_user_is_redirected_to_otp_screen_and_email_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login.otp'));
        $response->assertSessionHas('login_otp');

        Mail::assertSent(LoginOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_regular_user_can_authenticate_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $otpCode = '123456';

        $sessionData = [
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => $otpCode,
            'remember' => false,
            'expires_at' => now()->addMinutes(5)->timestamp,
            'last_sent_at' => now()->timestamp,
        ];

        $response = $this->withSession(['login_otp' => $sessionData])
            ->post('/login/otp', [
                'otp' => $otpCode,
            ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_regular_user_cannot_authenticate_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $sessionData = [
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => '123456',
            'remember' => false,
            'expires_at' => now()->addMinutes(5)->timestamp,
            'last_sent_at' => now()->timestamp,
        ];

        $response = $this->withSession(['login_otp' => $sessionData])
            ->post('/login/otp', [
                'otp' => '654321',
            ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('otp');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
