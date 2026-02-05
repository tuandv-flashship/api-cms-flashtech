<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class ChangePasswordTest extends ApiTestCase
{
    protected string $endpoint = '/v1/member/password';

    public function testChangePasswordSuccessfully(): void
    {
        $password = 'oldpassword';
        $member = Member::factory()->create([
            'password' => Hash::make($password),
        ])->refresh();

        $data = [
            'current_password' => $password,
            'new_password' => 'Newpassword1!',
            'new_password_confirmation' => 'Newpassword1!',
        ];

        $response = $this->actingAs($member, 'member')->postJson($this->endpoint, $data);

        $response->assertNoContent();

        $member->refresh();
        $this->assertTrue(Hash::check('Newpassword1!', $member->password));

        $this->assertDatabaseHas('member_activity_logs', [
            'member_id' => $member->id,
            'action' => 'update_security',
        ]);
    }

    public function testChangePasswordWithIncorrectCurrentPassword(): void
    {
        $member = Member::factory()->create([
            'password' => Hash::make('password'),
        ])->refresh();

        $data = [
            'current_password' => 'wrongpassword',
            'new_password' => 'Newpassword1!',
            'new_password_confirmation' => 'Newpassword1!',
        ];

        $response = $this->actingAs($member, 'member')->postJson($this->endpoint, $data);

        $response->assertStatus(400);
        // Ideally we should check for specific error message or status code if we handled exception properly
    }

    public function testChangePasswordUnauthenticated(): void
    {
        $data = [
            'current_password' => 'oldpassword',
            'new_password' => 'Newpassword1!',
            'new_password_confirmation' => 'Newpassword1!',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertUnauthorized();
    }
}
