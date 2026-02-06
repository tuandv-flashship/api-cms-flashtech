<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class PasswordResetTest extends ApiTestCase
{
    protected string $forgotEndpoint = '/v1/members/forgot-password';
    protected string $resetEndpoint = '/v1/members/reset-password';

    public function testForgotPasswordSendsLink(): void
    {
        $member = Member::factory()->create(['email' => 'forgot@test.com']);

        $response = $this->postJson($this->forgotEndpoint, [
            'email' => $member->email,
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Password reset link sent.']);

        $this->assertDatabaseHas('member_password_resets', [
            'email' => $member->email,
        ]);
    }

    public function testForgotPasswordWithUnknownEmailReturnsSuccessWithoutLeak(): void
    {
        $response = $this->postJson($this->forgotEndpoint, [
            'email' => 'unknown-member@test.com',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Password reset link sent.']);
    }

    public function testForgotPasswordWritesWarningLogWhenAuditThresholdReached(): void
    {
        Config::set('member.password_reset.audit.enabled', true);
        Config::set('member.password_reset.audit.window_minutes', 5);
        Config::set('member.password_reset.audit.warning_threshold', 1);
        Log::spy();

        $member = Member::factory()->create(['email' => 'audit@test.com']);

        $response = $this->postJson($this->forgotEndpoint, [
            'email' => $member->email,
        ]);

        $response->assertOk();
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Member password reset threshold exceeded'
                    && isset($context['email_hash'], $context['attempts'], $context['window_minutes'])
                    && $context['attempts'] >= 1
                    && $context['window_minutes'] === 5;
            });
    }

    public function testResetPasswordSuccessfully(): void
    {
        $member = Member::factory()->create(['email' => 'reset@test.com', 'password' => 'oldpassword']);
        $token = Password::broker('members')->createToken($member);

        $data = [
            'email' => $member->email,
            'token' => $token,
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ];

        $response = $this->postJson($this->resetEndpoint, $data);

        $response->assertOk();
        $response->assertJson(['message' => 'Password has been reset successfully.']);

        $member->refresh();
        $this->assertTrue(Hash::check('newPassword123!', $member->password));
        
        $this->assertDatabaseMissing('member_password_resets', [
            'email' => $member->email,
        ]);
    }

    public function testResetPasswordFailsWithInvalidToken(): void
    {
        $member = Member::factory()->create();
        
        $data = [
            'email' => $member->email,
            'token' => 'invalid-token',
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ];

        $response = $this->postJson($this->resetEndpoint, $data);

        $response->assertStatus(422);
        // Assert validation error message key if possible
    }

    public function testForgotPasswordDisabledReturnsForbidden(): void
    {
        Config::set('member.password_reset.enabled', false);

        $member = Member::factory()->create(['email' => 'disabled-forgot@test.com']);

        $response = $this->postJson($this->forgotEndpoint, [
            'email' => $member->email,
        ]);

        $response->assertStatus(403);
    }

    public function testResetPasswordDisabledReturnsForbidden(): void
    {
        Config::set('member.password_reset.enabled', false);

        $member = Member::factory()->create(['email' => 'disabled-reset@test.com']);

        $response = $this->postJson($this->resetEndpoint, [
            'email' => $member->email,
            'token' => 'any-token',
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
        ]);

        $response->assertStatus(403);
    }
}
