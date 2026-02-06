<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceKeyRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListDeviceKeysTask extends ParentTask
{
    public function __construct(
        private readonly DeviceKeyRepository $repository,
    ) {
    }

    public function run(int $deviceId): LengthAwarePaginator
    {
        return $this->repository
            ->scope(static fn ($query) => $query
                ->where('device_id', $deviceId)
                ->orderByDesc('last_used_at')
                ->orderByDesc('id'))
            ->addRequestCriteria()
            ->paginate();
    }
}
