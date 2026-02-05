<?php

namespace App\Containers\AppSection\Member\Events;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Events\Event as ParentEvent;
use Illuminate\Queue\SerializesModels;

class MemberRegistered extends ParentEvent
{
    use SerializesModels;

    public function __construct(
        public Member $member
    ) {
    }

    public function broadcastOn(): array
    {
        return [];
    }
}
