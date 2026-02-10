<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberActivityAction;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\FindMemberByEmailTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ForgotPasswordAction extends ParentAction
{
    public function __construct(
        private readonly FindMemberByEmailTask $findMemberByEmailTask,
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
    ) {
    }

    public function run(string $email): void
    {
        if (!config('member.password_reset.enabled')) {
            return;
        }

        $normalizedEmail = Str::lower(trim($email));
        $member = $this->findMemberByEmailTask->run($normalizedEmail);

        $this->recordAuditAttempt($email);

        $status = Password::broker('members')->sendResetLink([
            'email' => $normalizedEmail,
        ]);

        if (in_array($status, [
            Password::RESET_LINK_SENT,
            Password::INVALID_USER,
            Password::RESET_THROTTLED,
        ], true)) {
            if ($member) {
                $this->createMemberActivityLogTask->run([
                    'member_id' => $member->id,
                    'action' => MemberActivityAction::REQUEST_PASSWORD_RESET->value,
                ]);
            }

            return;
        }

        throw ValidationException::withMessages(['email' => __($status)]);
    }

    private function recordAuditAttempt(string $email): void
    {
        if (!config('member.password_reset.audit.enabled', true)) {
            return;
        }

        $windowMinutes = max(1, (int) config('member.password_reset.audit.window_minutes', 5));
        $warningThreshold = max(1, (int) config('member.password_reset.audit.warning_threshold', 20));

        $normalizedEmail = Str::lower(trim($email));
        $emailHash = hash('sha256', $normalizedEmail);
        $ip = request()?->ip() ?? 'unknown';

        $cacheKey = sprintf(
            'member:password-reset:attempt:%s:%s:%d',
            $ip,
            $emailHash,
            intdiv(time(), $windowMinutes * 60),
        );

        Cache::add($cacheKey, 0, now()->addMinutes($windowMinutes));
        $attempts = (int) Cache::increment($cacheKey);

        if ($attempts >= $warningThreshold) {
            Log::warning('Member password reset threshold exceeded', [
                'ip_address' => $ip,
                'email_hash' => $emailHash,
                'attempts' => $attempts,
                'window_minutes' => $windowMinutes,
            ]);
        }
    }
}
