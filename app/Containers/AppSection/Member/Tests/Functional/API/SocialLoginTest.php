<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Models\MemberSocialAccount;
use App\Containers\AppSection\Authentication\Data\Factories\ClientFactory;
use App\Containers\AppSection\Member\Tests\Functional\ApiTestCase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

class SocialLoginTest extends ApiTestCase
{
    protected string $endpoint = '/v1/member/auth';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('member.social.google.enabled', true);
        Config::set('member.social.facebook.enabled', true);
    }

    public function testRedirectToProvider(): void
    {
        $provider = 'google';
        
        // Mock Redirect
        $providerMock = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('redirect')->andReturnSelf();
        $providerMock->shouldReceive('getTargetUrl')->andReturn('https://accounts.google.com/o/oauth2/auth?client_id=...');

        Socialite::shouldReceive('driver')->with($provider)->andReturn($providerMock);

        $url = route('api_member_social_login_redirect', ['provider' => $provider]);
        $response = $this->getJson($url);

        $response->assertOk();
        $response->assertJsonStructure(['url']);
    }

    public function testHandleProviderCallbackCreateNewMember(): void
    {
        ClientFactory::memberClient();

        $provider = 'google';
        $email = 'social@test.com';

        $socialUser = new SocialiteUser();
        $socialUser->id = '123456789';
        $socialUser->name = 'Social User';
        $socialUser->email = $email;
        $socialUser->token = 'social-token';
        $socialUser->avatar = 'https://avatar.url';
        
        $providerMock = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with($provider)->andReturn($providerMock);

        $url = route('api_member_social_login_callback', ['provider' => $provider]);
        $response = $this->getJson($url);

        $response->assertOk();
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
        
        $this->assertDatabaseHas('members', ['email' => $email]);
        $this->assertDatabaseHas('member_social_accounts', [
            'provider' => $provider,
            'provider_id' => '123456789',
        ]);
    }

    public function testHandleProviderCallbackUsesExistingSocialAccountWhenEmailChanged(): void
    {
        ClientFactory::memberClient();

        $provider = 'google';
        $member = Member::create([
            'name' => 'Existing Social User',
            'email' => 'old-email@test.com',
            'password' => 'password123!',
            'status' => MemberStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        MemberSocialAccount::create([
            'member_id' => $member->id,
            'provider' => $provider,
            'provider_id' => 'provider-user-1',
            'token' => 'old-token',
            'avatar' => null,
        ]);

        $socialUser = new SocialiteUser();
        $socialUser->id = 'provider-user-1';
        $socialUser->name = 'Existing Social User';
        $socialUser->email = 'new-email@test.com';
        $socialUser->token = 'new-token';
        $socialUser->avatar = 'https://avatar.new';

        $providerMock = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with($provider)->andReturn($providerMock);

        $url = route('api_member_social_login_callback', ['provider' => $provider]);
        $response = $this->getJson($url);

        $response->assertOk();
        $response->assertCookie('memberRefreshToken');
        $response->assertJsonPath('data.email', 'old-email@test.com');

        $this->assertDatabaseCount('members', 1);
        $this->assertDatabaseHas('member_social_accounts', [
            'member_id' => $member->id,
            'provider' => $provider,
            'provider_id' => 'provider-user-1',
        ]);

        $updatedAccount = MemberSocialAccount::query()
            ->where('member_id', $member->id)
            ->where('provider', $provider)
            ->first();

        $this->assertNotNull($updatedAccount);
        $this->assertSame('new-token', $updatedAccount->token);
        $this->assertNotSame('new-token', (string) $updatedAccount->getRawOriginal('token'));
    }
}
