<?php

namespace App\Containers\AppSection\User\Models;

use App\Containers\AppSection\Authorization\Enums\Role as RoleEnum;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\User\Data\Collections\UserCollection;
use App\Containers\AppSection\User\Enums\Gender;
use App\Ship\Parents\Models\UserModel as ParentUserModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends ParentUserModel
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'birth',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'immutable_datetime',
        'password' => 'hashed',
        'gender' => Gender::class,
        'birth' => 'immutable_date',
    ];

    public function newCollection(array $models = []): UserCollection
    {
        return new UserCollection($models);
    }

    /**
     * Allows Passport to find the user by email (case-insensitive).
     */
    public function findForPassport(string $username): self|null
    {
        return self::query()->where('email', strtolower($username))->first();
    }

    public function isSuperAdmin(): bool
    {
        if ($this->email && in_array($this->email, config('appSection-authorization.super_admins', []))) {
            return true;
        }

        if (!$this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return false;
        }

        return true;
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'owner_id')
            ->where('owner_type', DeviceOwnerType::USER);
    }

    protected function email(): Attribute
    {
        return new Attribute(
            get: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
            set: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }
}
