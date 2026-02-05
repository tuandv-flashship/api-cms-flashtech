<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Authentication\Data\Factories\ClientFactory;
use App\Containers\AppSection\Authentication\Data\Factories\PasswordTokenFactory;
use App\Containers\AppSection\Authentication\Data\Factories\TokenAttributeFormatter;
use App\Containers\AppSection\Authentication\Values\RequestProxies\PasswordGrant\AccessTokenProxy;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Hash;

class LogoutMemberTest extends ApiTestCase
{
    public function testLogoutRevokesToken(): void
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

        $token = app(PasswordTokenFactory::class)->make(
            AccessTokenProxy::create(
                UserCredential::create($member->email, $password),
                $client,
            ),
        );

        $attributes = app(TokenAttributeFormatter::class)->format($token->accessToken);
        $tokenId = $attributes['oauth_access_token_id'] ?? null;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->accessToken)
            ->postJson(route('api_member_logout'));

        $response->assertAccepted();
        $response->assertCookieExpired('memberRefreshToken');

        $this->assertNotNull($tokenId);
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokenId,
            'revoked' => true,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'access_token_id' => $tokenId,
            'revoked' => true,
        ]);
    }

    public function testMobileLogoutRevokesToken(): void
    {
        $client = ClientFactory::memberMobileClient();
        $password = 'password123';
        $member = Member::create([
            'name' => 'John Doe',
            'email' => 'mobile-logout@test.com',
            'password' => Hash::make($password),
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $token = app(PasswordTokenFactory::class)->make(
            AccessTokenProxy::create(
                UserCredential::create($member->email, $password),
                $client,
            ),
        );

        $attributes = app(TokenAttributeFormatter::class)->format($token->accessToken);
        $tokenId = $attributes['oauth_access_token_id'] ?? null;

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->accessToken)
            ->withHeader('x-client', 'mobile')
            ->postJson(route('api_member_logout'));

        $response->assertAccepted();
        $response->assertCookieExpired('memberRefreshToken');

        $this->assertNotNull($tokenId);
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokenId,
            'revoked' => true,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'access_token_id' => $tokenId,
            'revoked' => true,
        ]);
    }
}
