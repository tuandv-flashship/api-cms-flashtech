<?php

namespace App\Containers\AppSection\Slug\Traits;

use App\Containers\AppSection\Slug\Models\Slug;
use App\Containers\AppSection\Slug\Supports\SlugRuntimeServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::deleted(function (Model $model): void {
            Slug::query()
                ->where('reference_type', $model::class)
                ->where('reference_id', $model->getKey())
                ->delete();
        });
    }

    public function slugable(): MorphOne
    {
        return $this->morphOne(Slug::class, 'reference')->select([
            'id',
            'key',
            'reference_type',
            'reference_id',
            'prefix',
        ]);
    }

    public function getSlugAttribute(): string
    {
        return $this->slugable?->key ?? '';
    }

    public function getSlugIdAttribute(): string
    {
        return (string) ($this->slugable?->getKey() ?? '');
    }

    public function getUrlAttribute(): string
    {
        $slug = $this->slugable;

        if (! $slug || ! $slug->key) {
            return '';
        }

        $helper = SlugRuntimeServices::helper();
        $prefix = $helper->getTranslator()->compile($slug->prefix);
        $path = ltrim($prefix . '/' . $slug->key, '/');

        return url($path) . $helper->getPublicSingleEndingURL();
    }
}
