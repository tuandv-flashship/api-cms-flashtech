<?php

namespace App\Containers\AppSection\Device\Data\Repositories;

use App\Containers\AppSection\Device\Models\Device;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Device
 *
 * @extends ParentRepository<TModel>
 */
final class DeviceRepository extends ParentRepository
{
    protected int $maxPaginationLimit = 200;

    protected $fieldSearchable = [
        'owner_type' => '=',
        'owner_id' => '=',
        'device_id' => '=',
        'platform' => 'like',
        'device_name' => 'like',
        'push_provider' => '=',
        'status' => '=',
    ];

    public function model(): string
    {
        return Device::class;
    }
}
