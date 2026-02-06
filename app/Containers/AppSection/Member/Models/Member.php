<?php

namespace App\Containers\AppSection\Member\Models;

use App\Containers\AppSection\Device\Models\Device;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Notifications\ResetPasswordNotification;
use App\Containers\AppSection\Member\Models\MemberActivityLog;
use App\Ship\Parents\Models\UserModel as ParentUserModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Member extends ParentUserModel
{
    protected $fillable = [
        'name',
        'username',
        'username_changed_at',
        'email',
        'password',
        'avatar_id',
        'dob',
        'phone',
        'description',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'username_changed_at' => 'datetime',
        'password' => 'hashed',
        'dob' => 'date',
        'status' => MemberStatus::class,
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'avatar_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(MemberSocialAccount::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(MemberActivityLog::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'owner_id')
            ->where('owner_type', DeviceOwnerType::MEMBER);
    }

    /**
     * Allows Passport to find the member by username/email (case-insensitive)
     * and enforce member login rules for password grant.
     */
    public function findForPassport(string $username): self|null
    {
        if (!config('member.auth.login_enabled', true)) {
            return null;
        }

        $login = self::normalizeLogin($username);
        $baseQuery = self::query()
            ->where('status', MemberStatus::ACTIVE);

        if (config('member.email_verification.enabled')) {
            $baseQuery->whereNotNull('email_verified_at');
        }

        return self::applyLoginIdentifierFilter((clone $baseQuery), $login)->first();
    }

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = config('member.password_reset.url');
        if ($resetUrl) {
            $separator = str_contains($resetUrl, '?') ? '&' : '?';
            $resetUrl .= $separator . http_build_query([
                'token' => $token,
                'email' => $this->getEmailForPasswordReset(),
            ]);
        }

        $this->notify(new ResetPasswordNotification($token, $resetUrl));
    }

    public function validateForPassportPasswordGrant($password): bool
    {
        if (Hash::check($password, (string) $this->password)) {
            return true;
        }

        $cached = Cache::pull($this->socialLoginTokenCacheKey());
        if (!$cached) {
            return false;
        }

        return Hash::check($password, $cached);
    }

    public function socialLoginTokenCacheKey(): string
    {
        return 'member_social_login_token:' . $this->getKey();
    }

    protected function username(): Attribute
    {
        return new Attribute(
            set: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }

    protected function email(): Attribute
    {
        return new Attribute(
            set: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }

    public static function generateUniqueUsername(string $base): string
    {
        $slug = Str::slug($base, '-');
        $slug = strtolower($slug);

        if ($slug === '') {
            $slug = 'member';
        }

        $maxLength = 191;
        $slug = substr($slug, 0, $maxLength);

        if (!self::query()->where('username', $slug)->exists()) {
            return $slug;
        }

        $suffix = 1;

        while (true) {
            $suffixString = '-' . $suffix;
            $availableLength = $maxLength - strlen($suffixString);
            $candidate = substr($slug, 0, max(1, $availableLength)) . $suffixString;

            if (!self::query()->where('username', $candidate)->exists()) {
                return $candidate;
            }

            $suffix++;

            if ($suffix > 9999) {
                $random = Str::lower(Str::random(6));
                $candidate = substr($slug, 0, max(1, $maxLength - 7)) . '-' . $random;

                return $candidate;
            }
        }
    }

    public static function applyLoginIdentifierFilter(Builder $query, string $login): Builder
    {
        $normalized = self::normalizeLogin($login);

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return $query->where('email', $normalized);
        }

        return $query->where('username', $normalized);
    }

    public static function normalizeLogin(string $login): string
    {
        return strtolower(trim($login));
    }
}
