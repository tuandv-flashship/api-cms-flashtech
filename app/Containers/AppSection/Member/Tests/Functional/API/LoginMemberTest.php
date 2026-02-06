<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Authentication\Data\Factories\ClientFactory;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Hash;

class LoginMemberTest extends ApiTestCase
{
    protected string $endpoint = '/v1/member/login';

    public function testLoginMemberSuccessfully(): void
    {
        ClientFactory::memberClient();

        $password = 'password123';
        $member = Member::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $data = [
            'email' => $member->email,
            'password' => $password,
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(200);
        $response->assertCookie('memberRefreshToken');
        $response->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'name',
                'email',
            ],
            'meta' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);
    }

    public function testLoginMemberWithUsername(): void
    {
        ClientFactory::memberClient();

        $password = 'password123';
        $member = Member::create([
            'name' => 'John Doe',
            'username' => 'johnny',
            'email' => 'johnny@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson($this->endpoint, [
            'login' => $member->username,
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'name',
                'email',
            ],
            'meta' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);
    }

    public function testLoginMemberWithInvalidPassword(): void
    {
        $member = Member::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => Hash::make('password123'),
            'status' => MemberStatus::ACTIVE,
        ]);

        $data = [
            'email' => $member->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials.',
        ]);
    }

    public function testLoginNonExistentMember(): void
    {
        $data = [
            'email' => 'nonexistent@test.com',
            'password' => 'password123',
        ];

        $response = $this->postJson($this->endpoint, $data);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials.',
            'error_code' => 'invalid_credentials',
        ]);
    }

    public function testLoginPendingMemberIsBlocked(): void
    {
        $password = 'password123';
        $member = Member::create([
            'name' => 'Pending User',
            'email' => 'pending@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::PENDING,
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson($this->endpoint, [
            'email' => $member->email,
            'password' => $password,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials.',
            'error_code' => 'invalid_credentials',
        ]);
    }

    public function testLoginInactiveMemberIsBlocked(): void
    {
        $password = 'password123';
        $member = Member::create([
            'name' => 'Inactive User',
            'email' => 'inactive@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::INACTIVE,
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson($this->endpoint, [
            'email' => $member->email,
            'password' => $password,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials.',
            'error_code' => 'invalid_credentials',
        ]);
    }
}
