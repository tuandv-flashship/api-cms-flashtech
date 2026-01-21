<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Data\Repositories\LanguageRepository;
use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteLanguageTask extends ParentTask
{
    public function __construct(
        private readonly LanguageRepository $repository,
    ) {
    }

    public function run(int $id): bool
    {
        $language = $this->repository->findOrFail($id);
        $wasDefault = (bool) $language->lang_is_default;

        $deleted = $this->repository->delete($id);

        if ($deleted && $wasDefault) {
            $replacement = Language::query()->orderBy('lang_order')->first();

            if ($replacement) {
                Language::query()
                    ->where('lang_id', $replacement->lang_id)
                    ->update(['lang_is_default' => 1]);
            }
        }

        return $deleted;
    }
}
