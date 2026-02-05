<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Authentication\Data\Factories\ClientFactory;
use App\Containers\AppSection\Authentication\Data\Factories\PasswordTokenFactory;
use App\Containers\AppSection\Authentication\Values\RequestProxies\PasswordGrant\AccessTokenProxy;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;

class RefreshMemberMobileTokenTest extends ApiTestCase
{
    public function testCanRefreshTokenMobile(): void
    {
        $client = ClientFactory::memberMobileClient();

        $password = 'password123';
        $member = Member::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $refreshToken = app(PasswordTokenFactory::class)->make(
            AccessTokenProxy::create(
                UserCredential::create($member->email, $password),
                $client,
            ),
        )->refreshToken->value();

        $response = $this->withHeader('x-client', 'mobile')->postJson(route('api_member_refresh_token'), [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json->has(
                'data',
                static fn (AssertableJson $json): AssertableJson => $json->hasAll([
                    'access_token',
                    'refresh_token',
                    'token_type',
                    'expires_in',
                ])->where('token_type', 'Bearer')
                    ->etc(),
            )->etc(),
        );
    }
}
