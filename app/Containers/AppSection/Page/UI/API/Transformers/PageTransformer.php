<?php

namespace App\Containers\AppSection\Page\UI\API\Transformers;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\User\UI\API\Transformers\UserTransformer;
use App\Ship\Parents\Transformers\Traits\HasOriginLang;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

final class PageTransformer extends ParentTransformer
{
    use HasOriginLang;

    protected array $availableIncludes = [
        'translations',
        'user',
    ];

    public function transform(Page $page): array
    {
        $mediaService = app(MediaService::class);

        return [
            'type' => $page->getResourceKey(),
            'id' => $page->getHashedKey(),
            'name' => $page->name,
            'description' => $page->description,
            'content' => $page->content,
            'status' => $page->status?->value ?? (string) $page->status,
            'image' => $mediaService->getImageUrl($page->image),
            'template' => $page->template,
            'slug' => $page->slug,
            'url' => $page->url,
            'seo_meta' => $page->getMeta('seo_meta'),
            'user_id' => $this->hashId($page->user_id),
            'origin_lang' => $this->getOriginLang(),
            'created_at' => $page->created_at?->toISOString(),
            'updated_at' => $page->updated_at?->toISOString(),
        ];
    }

    public function includeTranslations(Page $page): Collection
    {
        $langCode = $this->resolveLangCode();
        if ($langCode === null) {
            return $this->collection(collect(), new PageTranslationTransformer($page));
        }

        $page->loadMissing([
            'translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        $translations = $page->translations->where('lang_code', $langCode)->values();

        return $this->collection($translations, new PageTranslationTransformer($page));
    }

    public function includeUser(Page $page): Item
    {
        return $this->item($page->user, new UserTransformer());
    }

    private function resolveLangCode(): ?string
    {
        $langCode = request()->query('lang_code') ?? request()->query('language');
        if (! $langCode) {
            return null;
        }

        $normalized = LanguageAdvancedManager::normalizeLanguageCode((string) $langCode);

        return $normalized ?? (string) $langCode;
    }

    private function hashId(int|string|null $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        $intId = (int) $id;
        if ($intId <= 0) {
            return $intId;
        }

        return config('apiato.hash-id') ? hashids()->encodeOrFail($intId) : $intId;
    }
}
