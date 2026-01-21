<?php

namespace App\Containers\AppSection\Language\Models;

use App\Containers\AppSection\Language\Data\Collections\LanguageCollection;
use App\Ship\Parents\Models\Model as ParentModel;

final class Language extends ParentModel
{
    public $timestamps = false;

    protected $primaryKey = 'lang_id';

    protected $table = 'languages';

    protected $fillable = [
        'lang_name',
        'lang_locale',
        'lang_code',
        'lang_flag',
        'lang_is_default',
        'lang_order',
        'lang_is_rtl',
    ];

    protected $casts = [
        'lang_name' => 'string',
        'lang_locale' => 'string',
        'lang_code' => 'string',
        'lang_flag' => 'string',
        'lang_is_default' => 'bool',
        'lang_order' => 'int',
        'lang_is_rtl' => 'bool',
    ];

    public function newCollection(array $models = []): LanguageCollection
    {
        return new LanguageCollection($models);
    }
}
