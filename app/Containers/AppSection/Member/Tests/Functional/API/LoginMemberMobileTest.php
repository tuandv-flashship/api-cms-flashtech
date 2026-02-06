<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Authentication\Data\Factories\ClientFactory;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Hash;

class LoginMemberMobileTest extends ApiTestCase
{
    protected string $endpoint = '/v1/member/login';

    public function testLoginMemberMobileSuccessfully(): void
    {
        ClientFactory::memberMobileClient();

        $password = 'password123';
        $member = Member::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $response = $this->withHeader('x-client', 'mobile')->postJson($this->endpoint, [
            'email' => $member->email,
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
                'refresh_token',
                'expires_in',
            ],
        ]);
    }

    public function testLoginMemberMobileWithUsername(): void
    {
        ClientFactory::memberMobileClient();

        $password = 'password123';
        $member = Member::create([
            'name' => 'John Doe',
            'username' => 'johnny',
            'email' => 'johnny-mobile@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $response = $this->withHeader('x-client', 'mobile')->postJson($this->endpoint, [
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
                'refresh_token',
                'expires_in',
            ],
        ]);
    }
}
