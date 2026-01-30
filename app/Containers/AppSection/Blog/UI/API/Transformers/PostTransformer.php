<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldBoxTransformer;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\User\UI\API\Transformers\UserTransformer;
use App\Ship\Parents\Transformers\Traits\HasOriginLang;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

final class PostTransformer extends ParentTransformer
{
    use HasOriginLang;

    protected array $availableIncludes = [
        'categories',
        'tags',
        'author',
        'translations',
        'customFields',
    ];

    public function transform(Post $post): array
    {
        return [
            'type' => $post->getResourceKey(),
            'id' => $post->getHashedKey(),
            'name' => $post->name,
            'description' => $post->description,
            'content' => $post->content,
            'status' => $post->status?->value ?? (string) $post->status,
            'is_featured' => (bool) $post->is_featured,
            'image' => $post->image,
            'gallery' => $this->resolveGallery($post),
            'views' => $post->views,
            'format_type' => $post->format_type,
            'slug' => $post->slug,
            'url' => $post->url,
            'seo_meta' => $post->getMeta('seo_meta'),
            'author_id' => $this->hashId($post->author_id),
            'author_type' => $post->author_type,
            'category_ids' => $post->categories->map(fn ($category) => $category->getHashedKey())->values()->all(),
            'tag_ids' => $post->tags->map(fn ($tag) => $tag->getHashedKey())->values()->all(),
            'origin_lang' => $this->getOriginLang(),
            'created_at' => $post->created_at?->toISOString(),
            'updated_at' => $post->updated_at?->toISOString(),
        ];
    }

    public function includeCategories(Post $post): Collection
    {
        return $this->collection($post->categories, new CategoryTransformer());
    }

    public function includeTags(Post $post): Collection
    {
        return $this->collection($post->tags, new TagTransformer());
    }

    public function includeAuthor(Post $post): Item
    {
        return $this->item($post->author, new UserTransformer());
    }

    public function includeTranslations(Post $post): Collection
    {
        $langCode = $this->resolveLangCode();
        if ($langCode === null) {
            return $this->collection(collect(), new PostTranslationTransformer($post));
        }

        $post->loadMissing([
            'translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'galleryMeta.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        $translations = $post->translations->where('lang_code', $langCode)->values();

        return $this->collection($translations, new PostTranslationTransformer($post));
    }

    public function includeCustomFields(Post $post): Collection
    {
        $langCode = $this->resolveLangCode();
        $customFieldService = app(CustomFieldService::class);

        $customFieldsData = $customFieldService->exportCustomFieldsData(
            Post::class,
            (int) $post->getKey(),
            [],
            $langCode
        );

        return $this->collection($customFieldsData, new CustomFieldBoxTransformer());
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

    private function resolveGallery(Post $post): array
    {
        $meta = $post->relationLoaded('galleryMeta') ? $post->galleryMeta : null;

        return $meta ? ($meta->images ?? []) : [];
    }
}
