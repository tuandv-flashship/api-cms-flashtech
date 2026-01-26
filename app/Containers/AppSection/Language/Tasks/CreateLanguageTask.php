<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Data\Repositories\LanguageRepository;
use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateLanguageTask extends ParentTask
{
    public function __construct(
        private readonly LanguageRepository $repository,
    ) {
    }

    public function run(array $data): Language
    {
        $setDefault = array_key_exists('lang_is_default', $data) && (bool) $data['lang_is_default'];
        $hasDefault = Language::query()->where('lang_is_default', 1)->exists();

        if (! $hasDefault) {
            $data['lang_is_default'] = 1;
            $setDefault = true;
        }

        if ($setDefault) {
            Language::query()->where('lang_is_default', 1)->update(['lang_is_default' => 0]);
        }

        return $this->repository->create($data);
    }
}
