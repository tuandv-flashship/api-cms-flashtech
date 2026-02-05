<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Authentication\Data\Factories\ClientFactory;
use App\Containers\AppSection\Authentication\Data\Factories\PasswordTokenFactory;
use App\Containers\AppSection\Authentication\Values\RequestProxies\PasswordGrant\AccessTokenProxy;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Member\Values\MemberCsrfToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;

class RefreshMemberTokenTest extends ApiTestCase
{
    public function testCanRefreshToken(): void
    {
        $client = ClientFactory::memberClient();

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

        $csrfToken = MemberCsrfToken::generate();

        $response = $this
            ->withCredentials()
            ->disableCookieEncryption()
            ->withCookie('memberRefreshToken', $refreshToken)
            ->withCookie(MemberCsrfToken::cookieName(), $csrfToken)
            ->withHeader(MemberCsrfToken::headerName(), $csrfToken)
            ->postJson(route('api_member_refresh_token'));

        $response->assertOk();
        $response->assertCookie('memberRefreshToken');
        $response->assertHeader(MemberCsrfToken::headerName());
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json->has(
                'data',
                static fn (AssertableJson $json): AssertableJson => $json->hasAll([
                    'access_token',
                    'token_type',
                    'expires_in',
                ])->where('token_type', 'Bearer')
                    ->etc(),
            )->etc(),
        );
    }

    public function testRefreshRequiresToken(): void
    {
        $response = $this->postJson(route('api_member_refresh_token'));

        $response->assertForbidden();
    }

    public function testRefreshWithInvalidTokenReturnsErrorCode(): void
    {
        ClientFactory::memberClient();
        $csrfToken = MemberCsrfToken::generate();

        $response = $this
            ->withCredentials()
            ->withCookie(MemberCsrfToken::cookieName(), $csrfToken)
            ->withHeader(MemberCsrfToken::headerName(), $csrfToken)
            ->disableCookieEncryption()
            ->postJson(route('api_member_refresh_token'), [
                'refresh_token' => 'invalid-token',
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error_code' => 'refresh_token_invalid',
        ]);
    }
}
