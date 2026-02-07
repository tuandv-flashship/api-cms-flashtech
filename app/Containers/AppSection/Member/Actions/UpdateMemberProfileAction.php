<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Events\MemberRegistered;
use App\Containers\AppSection\Member\Models\Member;
use App\Containers\AppSection\Member\Tasks\CreateMemberActivityLogTask;
use App\Containers\AppSection\Member\Tasks\RevokeMemberTokensTask;
use App\Containers\AppSection\Member\Tasks\UpdateMemberTask;
use App\Containers\AppSection\Member\UI\API\Requests\UpdateMemberProfileRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Ship\Exceptions\IncorrectPasswordException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UpdateMemberProfileAction extends ParentAction
{
    public function __construct(
        private readonly UpdateMemberTask $updateMemberTask,
        private readonly RevokeMemberTokensTask $revokeMemberTokensTask,
        private readonly CreateMemberActivityLogTask $createMemberActivityLogTask,
    ) {
    }

    public function run(UpdateMemberProfileRequest $request): Member
    {
        $data = $request->validated();
        $member = Auth::guard('member')->user();
        $statusBefore = $member->status;

        if (array_key_exists('username', $data)) {
            $newUsername = $data['username'];

            if (is_null($newUsername) || $newUsername === '') {
                unset($data['username']);
            } else {
                $currentUsername = $member->username;
                $normalizedNew = strtolower($newUsername);
                $normalizedCurrent = strtolower((string) $currentUsername);

                if ($normalizedNew === $normalizedCurrent) {
                    unset($data['username']);
                } elseif (!empty($currentUsername)) {
                    throw ValidationException::withMessages([
                        'username' => 'Username cannot be changed once set.',
                    ]);
                } else {
                    $data['username_changed_at'] = now();
                }
            }
        }

        if (array_key_exists('email', $data)) {
            $newEmail = $data['email'];

            if (is_null($newEmail) || $newEmail === '') {
                unset($data['email']);
            } else {
                $currentEmail = $member->email;
                $normalizedNew = strtolower($newEmail);
                $normalizedCurrent = strtolower((string) $currentEmail);

                if ($normalizedNew === $normalizedCurrent) {
                    unset($data['email']);
                } elseif (!empty($currentEmail)) {
                    throw ValidationException::withMessages([
                        'email' => 'Email cannot be changed once set.',
                    ]);
                }
            }
        }

        $hasPasswordChange = array_key_exists('password', $data);

        if ($hasPasswordChange) {
            $currentPassword = (string) ($data['current_password'] ?? '');
            if (!Hash::check($currentPassword, $member->password)) {
                throw new IncorrectPasswordException();
            }

            unset($data['current_password']);
        } else {
            unset($data['current_password']);
        }

        $emailChanged = array_key_exists('email', $data) && $data['email'] !== $member->email;
        $isEmailVerificationEnabled = config('member.email_verification.enabled');

        if ($emailChanged) {
            if ($isEmailVerificationEnabled) {
                $data['email_verified_at'] = null;
                $data['status'] = MemberStatus::PENDING;
            } else {
                $data['email_verified_at'] = now();
            }
        }

        $hasAvatarChange = array_key_exists('avatar_id', $data);
        $hasProfileChanges = !empty(array_diff(array_keys($data), [
            'avatar_id',
            'password',
            'current_password',
            'username_changed_at',
            'email_verified_at',
            'status',
        ]));

        if ($data === []) {
            return $member;
        }

        $member = $this->updateMemberTask->run($member->id, $data);

        if ($emailChanged && $isEmailVerificationEnabled) {
            // Dispatch after persisting the new email to ensure the queued listener uses the correct address.
            MemberRegistered::dispatch($member);
        }

        $statusBecameInactive = $statusBefore === MemberStatus::ACTIVE
            && $member->status !== MemberStatus::ACTIVE;

        if ($hasPasswordChange || $statusBecameInactive) {
            $this->revokeMemberTokensTask->run($member);
        }

        if ($hasProfileChanges) {
            $this->createMemberActivityLogTask->run([
                'member_id' => $member->id,
                'action' => 'update_setting',
            ]);
        }

        if ($hasAvatarChange) {
            $this->createMemberActivityLogTask->run([
                'member_id' => $member->id,
                'action' => 'changed_avatar',
            ]);
        }

        if ($hasPasswordChange) {
            $this->createMemberActivityLogTask->run([
                'member_id' => $member->id,
                'action' => 'update_security',
            ]);
        }

        return $member;
    }
}
