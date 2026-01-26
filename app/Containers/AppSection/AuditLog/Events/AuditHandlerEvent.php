<?php

namespace App\Containers\AppSection\AuditLog\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

final class AuditHandlerEvent
{
    use SerializesModels;

    public string|int $referenceUser;

    public function __construct(
        public string $module,
        public string $action,
        public int|string $referenceId,
        public ?string $referenceName,
        public string $type,
        int|string $referenceUser = 0,
    ) {
        if ($referenceUser === 0) {
            $referenceUser = Auth::guard()->id() ?: Auth::guard('api')->id() ?: 0;
        }

        $this->referenceUser = $referenceUser;
    }
}
