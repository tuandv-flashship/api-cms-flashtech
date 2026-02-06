<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListDevicesTask extends ParentTask
{
    public function __construct(
        private readonly DeviceRepository $repository,
    ) {
    }

    public function run(DeviceOwnerType $ownerType, int $ownerId): LengthAwarePaginator
    {
        $ownerTypeValue = $ownerType->value;

        return $this->repository
            ->scope(static fn ($query) => $query
                ->where('owner_type', $ownerTypeValue)
                ->where('owner_id', $ownerId)
                ->orderByDesc('last_seen_at')
                ->orderByDesc('id'))
            ->addRequestCriteria()
            ->paginate();
    }
}
