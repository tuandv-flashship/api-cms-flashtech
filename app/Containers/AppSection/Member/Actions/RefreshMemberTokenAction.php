<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Authentication\Data\DTOs\PasswordToken;
use App\Containers\AppSection\Authentication\Data\Factories\PasswordTokenFactory;
use App\Containers\AppSection\Authentication\Values\Clients\MemberClient;
use App\Containers\AppSection\Authentication\Values\Clients\MemberMobileClient;
use App\Containers\AppSection\Authentication\Values\RefreshToken;
use App\Containers\AppSection\Authentication\Values\RequestProxies\PasswordGrant\RefreshTokenProxy;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Actions\Action as ParentAction;

final class RefreshMemberTokenAction extends ParentAction
{
    public function __construct(
        private readonly PasswordTokenFactory $factory,
    ) {
    }

    public function run(RefreshToken $refreshToken, string $clientType = MemberClientType::WEB): PasswordToken
    {
        $client = MemberClientType::isMobile($clientType)
            ? MemberMobileClient::create()
            : MemberClient::create();

        return $this->factory->make(
            RefreshTokenProxy::create(
                $refreshToken,
                $client,
            ),
        );
    }
}
