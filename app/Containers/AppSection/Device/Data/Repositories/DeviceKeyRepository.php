<?php

namespace App\Containers\AppSection\Device\Data\Repositories;

use App\Containers\AppSection\Device\Models\DeviceKey;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of DeviceKey
 *
 * @extends ParentRepository<TModel>
 */
final class DeviceKeyRepository extends ParentRepository
{
    protected int $maxPaginationLimit = 200;

    protected $fieldSearchable = [
        'device_id' => '=',
        'key_id' => '=',
        'status' => '=',
    ];

    public function model(): string
    {
        return DeviceKey::class;
    }
}
