<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Data\Repositories\LanguageRepository;
use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateLanguageTask extends ParentTask
{
    public function __construct(
        private readonly LanguageRepository $repository,
    ) {
    }

    public function run(int $id, array $data): Language
    {
        $setDefault = array_key_exists('lang_is_default', $data) && (bool) $data['lang_is_default'];

        $language = $this->repository->update($data, $id);

        if ($setDefault) {
            Language::query()
                ->where('lang_id', '!=', $language->lang_id)
                ->update(['lang_is_default' => 0]);
        }

        if (! Language::query()->where('lang_is_default', 1)->exists()) {
            Language::query()
                ->where('lang_id', $language->lang_id)
                ->update(['lang_is_default' => 1]);
            $language->lang_is_default = true;
        }

        return $language;
    }
}
