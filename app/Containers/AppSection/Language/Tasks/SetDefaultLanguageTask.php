<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Data\Repositories\LanguageRepository;
use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class SetDefaultLanguageTask extends ParentTask
{
    public function __construct(
        private readonly LanguageRepository $repository,
    ) {
    }

    public function run(int $id): Language
    {
        $language = $this->repository->findOrFail($id);

        Language::query()->update(['lang_is_default' => 0]);
        Language::query()
            ->where('lang_id', $language->lang_id)
            ->update(['lang_is_default' => 1]);

        $language->lang_is_default = true;

        return $language;
    }
}
