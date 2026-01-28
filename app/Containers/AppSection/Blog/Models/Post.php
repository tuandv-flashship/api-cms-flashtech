<?php

namespace App\Containers\AppSection\Blog\Models;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Containers\AppSection\MetaBox\Traits\HasMetaBoxes;
use App\Containers\AppSection\Revision\Traits\RevisionableTrait;
use App\Containers\AppSection\Slug\Traits\HasSlug;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Post extends ParentModel
{
    use HasSlug;
    use HasLanguageTranslations;
    use HasMetaBoxes;
    use RevisionableTrait;

    protected $table = 'posts';

    protected bool $revisionEnabled = true;
    protected bool $revisionCleanup = true;
    protected int $historyLimit = 20;
    protected array $dontKeepRevisionOf = [
        'content',
        'views',
    ];

    protected $fillable = [
        'name',
        'description',
        'content',
        'image',
        'is_featured',
        'format_type',
        'status',
        'author_id',
        'author_type',
        'views',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'is_featured' => 'bool',
        'views' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $post): void {
            $post->categories()->detach();
            $post->tags()->detach();
        });

        static::creating(function (self $post): void {
            $post->author_id = $post->author_id ?: auth()->id();
            $post->author_type = $post->author_type ?: User::class;
        });
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories');
    }

    public function author(): MorphTo
    {
        return $this->morphTo()->withDefault();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class, 'posts_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    public function getNameAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('name', $value);
    }

    public function getDescriptionAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('description', $value);
    }

    public function getContentAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('content', $value);
    }
}
