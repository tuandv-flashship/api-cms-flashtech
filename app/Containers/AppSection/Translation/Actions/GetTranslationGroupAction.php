<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\GetTranslationGroupFromDbTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class GetTranslationGroupAction extends ParentAction
{
    public function __construct(
        private readonly GetTranslationGroupFromDbTask $task,
    ) {
    }

    /**
     * @return array{items: list<array{group: string, key: string, en: string|null, value: string|null}>, total: int, page: int, per_page: int, last_page: int}
     */
    public function run(string $locale, ?string $group = null, ?string $search = null, int $page = 1, int $perPage = 20): array
    {
        return $this->task->run($locale, $group, $search, $page, $perPage);
    }
}
