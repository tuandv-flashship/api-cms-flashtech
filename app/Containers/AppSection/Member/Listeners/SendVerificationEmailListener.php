<?php

namespace App\Containers\AppSection\Member\Listeners;

use App\Containers\AppSection\Member\Actions\SendVerificationEmailAction;
use App\Containers\AppSection\Member\Events\MemberRegistered;
use App\Ship\Parents\Listeners\Listener as ParentListener;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVerificationEmailListener extends ParentListener implements ShouldQueue
{
    public function handle(MemberRegistered $event): void
    {
        app(SendVerificationEmailAction::class)->run($event->member);
    }
}
