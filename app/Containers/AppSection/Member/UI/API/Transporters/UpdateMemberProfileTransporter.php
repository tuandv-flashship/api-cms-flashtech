<?php

namespace App\Containers\AppSection\Member\UI\API\Transporters;

use App\Ship\Parents\Transporters\Transporter;

/**
 * UpdateMemberProfileTransporter
 * 
 * @property string|null $name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $current_password
 * @property string|null $password
 * @property string|null $dob
 * @property string|null $phone
 * @property string|null $description
 * @property string|null $avatar_id
 */
final class UpdateMemberProfileTransporter extends Transporter
{
    public function getProfileData(): array
    {
        return array_filter($this->onlyAsArray([
            'name',
            'dob',
            'phone',
            'description',
            'avatar_id',
        ]), fn ($value) => $value !== null);
    }

    public function getAccountData(): array
    {
        return array_filter($this->onlyAsArray([
            'username',
            'email',
        ]), fn ($value) => $value !== null);
    }

    public function getSecurityData(): array
    {
        return array_filter($this->onlyAsArray([
            'current_password',
            'password',
        ]), fn ($value) => $value !== null);
    }

    /**
     * Get all update data merged
     */
    public function getAllData(): array
    {
        return array_filter($this->toArray(), fn ($value) => $value !== null);
    }
}
