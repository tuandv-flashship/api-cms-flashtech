<?php

namespace App\Containers\AppSection\LanguageAdvanced\Tests\Unit;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\PostTranslation;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\LanguageAdvanced\Tests\ContainerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageAdvancedManager::class)]
final class HasLanguageTranslationsTest extends ContainerTestCase
{
    public function testCascadeDeleteRemovesTranslations(): void
    {
        $post = Post::factory()->createOne(['name' => 'Original']);

        // Insert translation directly
        PostTranslation::query()->insert([
            'lang_code' => $this->secondaryLanguage->lang_code,
            'posts_id' => $post->id,
            'name' => 'Translated Name',
            'description' => 'Translated Desc',
            'content' => 'Translated Content',
        ]);

        $this->assertDatabaseHas('posts_translations', [
            'posts_id' => $post->id,
            'lang_code' => $this->secondaryLanguage->lang_code,
        ]);

        $post->delete();

        $this->assertDatabaseMissing('posts_translations', [
            'posts_id' => $post->id,
        ]);
    }

    public function testAutoTranslateReturnsTranslatedValueForNonDefaultLocale(): void
    {
        $post = Post::factory()->createOne(['name' => 'Tên Tiếng Việt']);

        PostTranslation::query()->insert([
            'lang_code' => $this->secondaryLanguage->lang_code,
            'posts_id' => $post->id,
            'name' => 'English Name',
            'description' => 'English Desc',
            'content' => 'English Content',
        ]);

        // Set non-default locale
        LanguageAdvancedManager::setTranslationLocale($this->secondaryLanguage->lang_code);

        // Reload post with translations (required for translateAttribute)
        $post = Post::query()->with('translations')->find($post->id);

        $this->assertSame('English Name', $post->name);
        $this->assertSame('English Desc', $post->description);
        $this->assertSame('English Content', $post->content);
    }

    public function testAutoTranslateReturnsOriginalValueForDefaultLocale(): void
    {
        $post = Post::factory()->createOne([
            'name' => 'Tên Tiếng Việt',
            'description' => 'Mô tả TV',
        ]);

        // Default locale = vi (set in ContainerTestCase)
        LanguageAdvancedManager::setTranslationLocale($this->defaultLanguage->lang_code);

        $post = Post::query()->with('translations')->find($post->id);

        $this->assertSame('Tên Tiếng Việt', $post->name);
        $this->assertSame('Mô tả TV', $post->description);
    }

    public function testAutoTranslateFallsBackToOriginalWhenNoTranslationExists(): void
    {
        $post = Post::factory()->createOne(['name' => 'Tên Tiếng Việt']);

        // Set non-default locale but no translation exists
        LanguageAdvancedManager::setTranslationLocale($this->secondaryLanguage->lang_code);

        $post = Post::query()->with('translations')->find($post->id);

        // Should fallback to original value
        $this->assertSame('Tên Tiếng Việt', $post->name);
    }

    public function testAutoTranslateLazyLoadsWhenNotEagerLoaded(): void
    {
        $post = Post::factory()->createOne(['name' => 'Tên Tiếng Việt']);

        PostTranslation::query()->insert([
            'lang_code' => $this->secondaryLanguage->lang_code,
            'posts_id' => $post->id,
            'name' => 'English Name',
            'description' => null,
            'content' => null,
        ]);

        LanguageAdvancedManager::setTranslationLocale($this->secondaryLanguage->lang_code);

        // Load WITHOUT eager-loading translations
        $post = Post::query()->find($post->id);

        // Should auto lazy-load translations and return translated value
        $this->assertSame('English Name', $post->name);
    }

    public function testTranslationsRelationReturnsCorrectRecords(): void
    {
        $post = Post::factory()->createOne();

        PostTranslation::query()->insert([
            'lang_code' => $this->secondaryLanguage->lang_code,
            'posts_id' => $post->id,
            'name' => 'EN Name',
            'description' => 'EN Desc',
            'content' => 'EN Content',
        ]);

        $translations = $post->translations;

        $this->assertCount(1, $translations);
        $this->assertSame('EN Name', $translations->first()->name);
        $this->assertSame($this->secondaryLanguage->lang_code, $translations->first()->lang_code);
    }

    public function testSaveTranslationCreatesNewRecord(): void
    {
        $post = Post::factory()->createOne(['name' => 'Original']);

        $result = LanguageAdvancedManager::saveTranslation($post, [
            'name' => 'Translated Name',
            'description' => 'Translated Desc',
            'content' => 'Translated Content',
        ], $this->secondaryLanguage->lang_code);

        $this->assertTrue($result);
        $this->assertDatabaseHas('posts_translations', [
            'posts_id' => $post->id,
            'lang_code' => $this->secondaryLanguage->lang_code,
            'name' => 'Translated Name',
        ]);
    }

    public function testSaveTranslationUpdatesExistingRecord(): void
    {
        $post = Post::factory()->createOne();

        // Create initial translation
        LanguageAdvancedManager::saveTranslation($post, [
            'name' => 'First Version',
        ], $this->secondaryLanguage->lang_code);

        // Update it
        LanguageAdvancedManager::saveTranslation($post, [
            'name' => 'Updated Version',
        ], $this->secondaryLanguage->lang_code);

        $this->assertDatabaseCount('posts_translations', 1);
        $this->assertDatabaseHas('posts_translations', [
            'posts_id' => $post->id,
            'name' => 'Updated Version',
        ]);
    }
}
