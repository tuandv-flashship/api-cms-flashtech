<?php

namespace App\Containers\AppSection\Member\Tests\Functional\API;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
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
                'object',
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
}
