<?php

namespace App\Containers\AppSection\LanguageAdvanced\Tests\Unit;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Supports\LanguageLocaleCache;
use App\Containers\AppSection\LanguageAdvanced\Tests\ContainerTestCase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageLocaleCache::class)]
final class LanguageLocaleCacheTest extends ContainerTestCase
{
    public function testNormalizeLanguageCodeWithLangCode(): void
    {
        $result = LanguageLocaleCache::normalizeLanguageCode('vi');

        $this->assertSame('vi', $result);
    }

    public function testNormalizeLanguageCodeWithLangLocale(): void
    {
        // en_US has lang_locale='en', lang_code='en_US'
        $result = LanguageLocaleCache::normalizeLanguageCode('en');

        $this->assertSame('en_US', $result);
    }

    public function testNormalizeLanguageCodeWithInvalidValue(): void
    {
        $result = LanguageLocaleCache::normalizeLanguageCode('invalid_code');

        $this->assertNull($result);
    }

    public function testNormalizeLanguageCodeWithNull(): void
    {
        $result = LanguageLocaleCache::normalizeLanguageCode(null);

        $this->assertNull($result);
    }

    public function testGetDefaultLocaleCode(): void
    {
        // Ensure cache is clear before this assertion
        LanguageLocaleCache::clearCache();

        $result = LanguageLocaleCache::getDefaultLocaleCode();

        $this->assertSame('vi', $result);
    }

    public function testGetDefaultLocaleCodeReflectsChange(): void
    {
        // Change default to en_US
        Language::query()->update(['lang_is_default' => false]);
        $this->secondaryLanguage->update(['lang_is_default' => true]);

        LanguageLocaleCache::clearCache();

        $result = LanguageLocaleCache::getDefaultLocaleCode();

        $this->assertSame('en_US', $result);
    }

    public function testClearCacheInvalidatesLocaleMap(): void
    {
        // Warm the cache
        LanguageLocaleCache::normalizeLanguageCode('vi');

        // Add a new language
        Language::query()->create([
            'lang_name' => 'Japanese',
            'lang_locale' => 'ja',
            'lang_code' => 'ja',
            'lang_flag' => 'jp',
            'lang_is_default' => false,
            'lang_order' => 2,
            'lang_is_rtl' => false,
        ]);

        // Before clearing cache, new language not found
        $this->assertNull(LanguageLocaleCache::normalizeLanguageCode('ja'));

        // After clearing, it's found
        LanguageLocaleCache::clearCache();
        $this->assertSame('ja', LanguageLocaleCache::normalizeLanguageCode('ja'));
    }

    public function testResolveToLangLocale(): void
    {
        // 'en_US' (lang_code) should resolve to 'en' (lang_locale)
        $result = LanguageLocaleCache::resolveToLangLocale('en_US');

        $this->assertSame('en', $result);
    }

    public function testResolveToLangLocaleWithLocale(): void
    {
        // 'vi' (lang_locale) should resolve to 'vi' (lang_locale)
        $result = LanguageLocaleCache::resolveToLangLocale('vi');

        $this->assertSame('vi', $result);
    }

    public function testResolveToLangLocaleWithInvalid(): void
    {
        $result = LanguageLocaleCache::resolveToLangLocale('invalid');

        $this->assertNull($result);
    }
}
