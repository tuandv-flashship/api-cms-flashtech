<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Events\MemberRegistered;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\FindMemberByIdTask;
use App\Containers\AppSection\Member\Tasks\RevokeMemberTokensTask;
use App\Containers\AppSection\Member\Tasks\UpdateMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\Admin\UpdateMemberRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdateMemberAction extends ParentAction
{
    public function run(UpdateMemberRequest $request): Member
    {
        $data = $request->validated();
        
        // Admin updates member, so we find by ID
        $member = app(FindMemberByIdTask::class)->run($request->id);
        $statusBefore = $member->status;
        $sendVerification = (bool) ($data['send_verification'] ?? false);
        unset($data['send_verification']);

        $emailChanged = array_key_exists('email', $data)
            && strtolower((string) $data['email']) !== strtolower((string) $member->email);
        $hasPasswordChange = array_key_exists('password', $data);

        if (array_key_exists('username', $data) && empty($data['username'])) {
            $baseSource = $data['email'] ?? $member->email ?? $data['name'] ?? $member->name ?? 'member';
            $base = str_contains($baseSource, '@') ? Str::before($baseSource, '@') : $baseSource;
            $data['username'] = Member::generateUniqueUsername($base);
        }

        $usernameChanged = array_key_exists('username', $data)
            && !empty($data['username'])
            && strtolower((string) $data['username']) !== strtolower((string) $member->username);

        if ($hasPasswordChange) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($emailChanged) {
            if (config('member.email_verification.enabled') && $sendVerification) {
                $data['email_verified_at'] = null;

                if (!array_key_exists('status', $data) || $data['status'] === MemberStatus::ACTIVE || $data['status'] === MemberStatus::ACTIVE->value) {
                    $data['status'] = MemberStatus::PENDING;
                }
            } else {
                $data['email_verified_at'] = now();
            }
        }

        $member = app(UpdateMemberTask::class)->run($member, $data);

        if ($emailChanged && config('member.email_verification.enabled') && $sendVerification) {
            MemberRegistered::dispatch($member);
        }

        $statusBecameInactive = $statusBefore === MemberStatus::ACTIVE
            && $member->status !== MemberStatus::ACTIVE;

        if ($hasPasswordChange || $statusBecameInactive) {
            app(RevokeMemberTokensTask::class)->run($member);
        }

        $adminId = Auth::guard('api')->id();
        $referenceName = $adminId ? 'admin:' . $adminId : null;

        if ($emailChanged) {
            app(CreateMemberActivityLogTask::class)->run([
                'member_id' => $member->id,
                'action' => 'admin_update_email',
                'reference_name' => $referenceName,
            ]);
        }

        if ($usernameChanged) {
            app(CreateMemberActivityLogTask::class)->run([
                'member_id' => $member->id,
                'action' => 'admin_update_username',
                'reference_name' => $referenceName,
            ]);
        }

        return $member;
    }
}
