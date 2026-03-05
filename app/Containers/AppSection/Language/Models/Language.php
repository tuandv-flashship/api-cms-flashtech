<?php

namespace App\Containers\AppSection\Language\Models;

use App\Containers\AppSection\Language\Data\Collections\LanguageCollection;
use App\Containers\AppSection\Language\Data\Factories\LanguageFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class Language extends ParentModel
{
    /** @use HasFactory<LanguageFactory> */
    use HasFactory;
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

    protected static function newFactory(): LanguageFactory
    {
        return LanguageFactory::new();
    }
}
