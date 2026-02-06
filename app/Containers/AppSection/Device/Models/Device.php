<?php

namespace App\Containers\AppSection\Device\Models;

use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Containers\AppSection\Device\Enums\DeviceStatus;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Device extends ParentModel
{
    protected $table = 'devices';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'device_id',
        'platform',
        'device_name',
        'push_token',
        'push_token_hash',
        'push_provider',
        'app_version',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'owner_type' => DeviceOwnerType::class,
        'status' => DeviceStatus::class,
        'last_seen_at' => 'datetime',
    ];

    public function keys(): HasMany
    {
        return $this->hasMany(DeviceKey::class);
    }
}
