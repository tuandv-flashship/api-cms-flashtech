<?php

namespace App\Containers\AppSection\Member\Providers;

use App\Containers\AppSection\Member\Events\MemberRegistered;
use App\Containers\AppSection\Member\Listeners\SendVerificationEmailListener;
use App\Ship\Parents\Providers\EventServiceProvider as ParentEventServiceProvider;

class EventServiceProvider extends ParentEventServiceProvider
{
    protected $listen = [
        MemberRegistered::class => [
            SendVerificationEmailListener::class,
        ],
    ];
}
