<?php

namespace App\Containers\AppSection\Language\Data\Repositories;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Language
 *
 * @extends ParentRepository<TModel>
 */
final class LanguageRepository extends ParentRepository
{
    protected $fieldSearchable = [
        'lang_id' => '=',
        'lang_name' => 'like',
        'lang_locale' => 'like',
        'lang_code' => 'like',
        'lang_is_default' => '=',
        'lang_is_rtl' => '=',
    ];

    public function model(): string
    {
        return Language::class;
    }
}
