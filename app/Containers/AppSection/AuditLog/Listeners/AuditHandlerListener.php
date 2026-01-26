<?php

namespace App\Containers\AppSection\AuditLog\Listeners;

use App\Containers\AppSection\AuditLog\Events\AuditHandlerEvent;
use App\Containers\AppSection\AuditLog\Models\AuditHistory;
use App\Containers\AppSection\Setting\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class AuditHandlerListener
{
    private const DEFAULT_EXCLUDED_KEYS = [
        'username',
        'password',
        're_password',
        'new_password',
        'current_password',
        'password_confirmation',
        '_token',
        'token',
        'refresh_token',
        'remember_token',
        'client_secret',
        'client_id',
        'api_key',
        'access_key',
        'secret_key',
        'otp',
        'pin',
    ];

    public function __construct(private readonly Request $request)
    {
    }

    public function handle(AuditHandlerEvent $event): void
    {
        try {
            $module = strtolower(Str::afterLast($event->module, '\\'));
            $user = $this->request->user();

            $data = [
                'user_agent' => $this->request->userAgent(),
                'ip_address' => $this->request->ip(),
                'module' => $module,
                'action' => $event->action,
                'user_id' => $user ? (int) $user->getKey() : 0,
                'user_type' => $user ? $user::class : null,
                'actor_id' => (int) $event->referenceUser,
                'actor_type' => $user ? $user::class : null,
                'reference_id' => $event->referenceId,
                'reference_name' => $event->referenceName ?? '',
                'type' => $event->type,
            ];

            if (! in_array($event->action, ['loggedin', 'password'], true)) {
                $excluded = config('audit-log.excluded_request_keys', []);
                if (! is_array($excluded)) {
                    $excluded = [];
                }

                $excluded = array_values(array_unique(array_merge(self::DEFAULT_EXCLUDED_KEYS, $excluded)));
                $data['request'] = json_encode($this->request->except($excluded));
            }

            AuditHistory::query()->create($data);
        } catch (Throwable) {
            // Avoid impacting the request if audit logging fails.
        }
    }
}
