<?php

namespace App\Containers\AppSection\Member\Actions;

use App\Containers\AppSection\Member\Enums\MemberActivityAction;
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

    public function run(\App\Containers\AppSection\Member\UI\API\Transporters\UpdateMemberProfileTransporter $data): Member
    {
        $member = Auth::guard('member')->user();
        $statusBefore = $member->status;

        // Clone data to modify
        $updateData = $data->toArray();

        if ($data->has('username')) {
            $newUsername = $data->username;

            if (is_null($newUsername) || $newUsername === '') {
                unset($updateData['username']);
            } else {
                $currentUsername = $member->username;
                $normalizedNew = strtolower($newUsername);
                $normalizedCurrent = strtolower((string) $currentUsername);

                if ($normalizedNew === $normalizedCurrent) {
                    unset($updateData['username']);
                } elseif (!empty($currentUsername)) {
                    throw ValidationException::withMessages([
                        'username' => 'Username cannot be changed once set.',
                    ]);
                } else {
                    $updateData['username_changed_at'] = now();
                }
            }
        }

        if ($data->has('email')) {
            $newEmail = $data->email;

            if (is_null($newEmail) || $newEmail === '') {
                unset($updateData['email']);
            } else {
                $currentEmail = $member->email;
                $normalizedNew = strtolower($newEmail);
                $normalizedCurrent = strtolower((string) $currentEmail);

                if ($normalizedNew === $normalizedCurrent) {
                    unset($updateData['email']);
                } elseif (!empty($currentEmail)) {
                    throw ValidationException::withMessages([
                        'email' => 'Email cannot be changed once set.',
                    ]);
                }
            }
        }

        $hasPasswordChange = $data->has('password');

        if ($hasPasswordChange) {
            $currentPassword = (string) ($updateData['current_password'] ?? '');
            if (!Hash::check($currentPassword, $member->password)) {
                throw new IncorrectPasswordException();
            }

            unset($updateData['current_password']);
        } else {
            unset($updateData['current_password']);
        }

        $emailChanged = $data->has('email') && $data->email !== $member->email;
        $isEmailVerificationEnabled = config('member.email_verification.enabled');

        if ($emailChanged) {
            if ($isEmailVerificationEnabled) {
                $updateData['email_verified_at'] = null;
                $updateData['status'] = MemberStatus::PENDING;
            } else {
                $updateData['email_verified_at'] = now();
            }
        }

        $hasAvatarChange = $data->has('avatar_id');
        $hasProfileChanges = !empty(array_diff(array_keys($updateData), [
            'avatar_id',
            'password',
            'current_password',
            'username_changed_at',
            'email_verified_at',
            'status',
        ]));

        if ($updateData === []) {
            return $member;
        }

        $member = $this->updateMemberTask->run($member->id, $updateData);

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
                'action' => MemberActivityAction::UPDATE_SETTING->value,
            ]);
        }

        if ($hasAvatarChange) {
            $this->createMemberActivityLogTask->run([
                'member_id' => $member->id,
                'action' => MemberActivityAction::CHANGED_AVATAR->value,
            ]);
        }

        if ($hasPasswordChange) {
            $this->createMemberActivityLogTask->run([
                'member_id' => $member->id,
                'action' => MemberActivityAction::UPDATE_SECURITY->value,
            ]);
        }

        return $member;
    }
}
