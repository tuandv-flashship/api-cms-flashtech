<?php

namespace App\Containers\AppSection\Setting\Actions;

use App\Containers\AppSection\Setting\Tasks\UpsertSettingsTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdatePhoneNumberSettingsAction extends ParentAction
{
    public function __construct(
        private readonly UpsertSettingsTask $upsertSettingsTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): void
    {
        $this->upsertSettingsTask->run($data);
    }
}
