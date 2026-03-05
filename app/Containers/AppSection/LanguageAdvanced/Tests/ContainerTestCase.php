<?php

namespace App\Containers\AppSection\LanguageAdvanced\Tests;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Supports\LanguageLocaleCache;
use App\Ship\Parents\Tests\TestCase;

class ContainerTestCase extends TestCase
{
    protected Language $defaultLanguage;
    protected Language $secondaryLanguage;

    protected function setUp(): void
    {
        parent::setUp();

        LanguageLocaleCache::clearCache();

        $this->defaultLanguage = Language::query()->firstOrCreate(
            ['lang_code' => 'vi'],
            [
                'lang_name' => 'Vietnamese',
                'lang_locale' => 'vi',
                'lang_flag' => 'vn',
                'lang_is_default' => true,
                'lang_order' => 0,
                'lang_is_rtl' => false,
            ],
        );

        $this->secondaryLanguage = Language::query()->firstOrCreate(
            ['lang_code' => 'en_US'],
            [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_flag' => 'us',
                'lang_is_default' => false,
                'lang_order' => 1,
                'lang_is_rtl' => false,
            ],
        );

        // Ensure default flag is correct using query builder (Language has non-standard PK 'lang_id')
        Language::query()->update(['lang_is_default' => false]);
        Language::query()
            ->where('lang_id', $this->defaultLanguage->lang_id)
            ->update(['lang_is_default' => true]);

        $this->defaultLanguage->refresh();

        LanguageLocaleCache::clearCache();
    }
}
