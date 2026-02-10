<?php

namespace App\Containers\AppSection\Device\Tasks;

use App\Containers\AppSection\Device\Data\Repositories\DeviceRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class NullifyDevicesByPushTokenHashTask extends ParentTask
{
    public function __construct(
        private readonly DeviceRepository $repository,
    ) {
    }

    public function run(string $pushProvider, string $pushTokenHash, int|null $excludeDeviceId = null): int
    {
        $query = $this->repository->getModel()->newQuery()
            ->where('push_provider', $pushProvider)
            ->where('push_token_hash', $pushTokenHash);

        if ($excludeDeviceId !== null) {
            $query = $query->where('id', '<>', $excludeDeviceId);
        }

        return $query->update([
            'push_token' => null,
            'push_token_hash' => null,
        ]);
    }
}
