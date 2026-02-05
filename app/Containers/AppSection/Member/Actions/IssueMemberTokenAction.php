<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Authentication\Data\DTOs\PasswordToken;
use App\Containers\AppSection\Authentication\Data\Factories\PasswordTokenFactory;
use App\Containers\AppSection\Authentication\Values\Clients\MemberClient;
use App\Containers\AppSection\Authentication\Values\Clients\MemberMobileClient;
use App\Containers\AppSection\Authentication\Values\RequestProxies\PasswordGrant\AccessTokenProxy;
use App\Containers\AppSection\Authentication\Values\UserCredential;
use App\Containers\AppSection\Member\Values\MemberClientType;
use App\Ship\Parents\Actions\Action as ParentAction;

final class IssueMemberTokenAction extends ParentAction
{
    public function __construct(
        private readonly PasswordTokenFactory $factory,
    ) {
    }

    public function run(UserCredential $credential, string $clientType = MemberClientType::WEB): PasswordToken
    {
        $client = MemberClientType::isMobile($clientType)
            ? MemberMobileClient::create()
            : MemberClient::create();

        return $this->factory->make(
            AccessTokenProxy::create(
                $credential,
                $client,
            ),
        );
    }
}
