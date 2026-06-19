<?php

namespace App\Containers\AppSection\User\Models;

use App\Containers\AppSection\Authorization\Enums\Role as RoleEnum;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\User\Data\Collections\UserCollection;
use App\Containers\AppSection\User\Enums\Gender;
use App\Containers\AppSection\User\Enums\UserStatus;
use App\Ship\Parents\Models\UserModel as ParentUserModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends ParentUserModel
{
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'gender',
        'birth',
        'avatar_id',
        'phone',
        'description',
        'status',
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
        'status' => UserStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->username) && $user->email) {
                $user->username = self::generateUniqueUsername($user->email);
            }
        });
    }

    /**
     * Generate a unique username from an email address.
     */
    public static function generateUniqueUsername(string $email): string
    {
        $base = strtolower(strstr($email, '@', true) ?: $email);
        $base = preg_replace('/[^a-z0-9._-]/', '', $base) ?: 'user';
        $candidate = $base;
        $suffix = 1;

        while (self::query()->where('username', $candidate)->exists()) {
            $candidate = $base . $suffix++;
        }

        return $candidate;
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'avatar_id');
    }

    public function newCollection(array $models = []): UserCollection
    {
        return new UserCollection($models);
    }

    /**
     * Allows Passport to find the user by email or username (case-insensitive).
     * Uses filter_var to detect email format, then queries the appropriate column first.
     */
    public function findForPassport(string $username): self|null
    {
        $identifier = strtolower($username);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // Query the most likely column first for optimal index usage
        return self::query()->where($isEmail ? 'email' : 'username', $identifier)->first()
            ?? self::query()->where($isEmail ? 'username' : 'email', $identifier)->first();
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

    protected function username(): Attribute
    {
        return new Attribute(
            get: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
            set: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }

    protected function email(): Attribute
    {
        return new Attribute(
            get: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
            set: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }
}
