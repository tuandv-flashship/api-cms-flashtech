<?php

namespace App\Containers\AppSection\Blog\Models;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Containers\AppSection\MetaBox\Traits\HasMetaBoxes;
use App\Containers\AppSection\Slug\Traits\HasSlug;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Casts\SafeContent;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

final class Category extends ParentModel
{
    use HasSlug;
    use HasLanguageTranslations;
    use HasMetaBoxes;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'icon',
        'is_featured',
        'order',
        'is_default',
        'status',
        'author_id',
        'author_type',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'is_featured' => 'bool',
        'is_default' => 'bool',
        'order' => 'int',
        'name' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $category): void {
            $category->children()->each(fn (self $child) => $child->delete());
            $category->posts()->detach();
            CustomField::query()
                ->where('use_for', self::class)
                ->where('use_for_id', $category->getKey())
                ->each(static fn (CustomField $field) => $field->delete());
        });

        static::creating(function (self $category): void {
            $category->author_id = $category->author_id ?: auth()->id();
            $category->author_type = $category->author_type ?: User::class;
        });
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_categories');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->wherePublished()->with('activeChildren');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'categories_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    /**
     * @return array<int, int>
     */
    public static function getChildrenIds(EloquentCollection $children, array $categoryIds = []): array
    {
        if ($children->isEmpty()) {
            return $categoryIds;
        }

        foreach ($children as $item) {
            $categoryIds[] = $item->getKey();
            if ($item->children->isNotEmpty()) {
                $categoryIds = static::getChildrenIds($item->activeChildren, $categoryIds);
            }
        }

        return $categoryIds;
    }

    public function parentsCollection(): Collection
    {
        $parents = collect();
        $parent = $this->parent;

        while ($parent && $parent->getKey()) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }

    public function getNameAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('name', $value);
    }

    public function getDescriptionAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('description', $value);
    }
}
