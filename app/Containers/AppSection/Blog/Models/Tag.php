<?php

namespace App\Containers\AppSection\Blog\Models;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Containers\AppSection\MetaBox\Traits\HasMetaBoxes;
use App\Containers\AppSection\Slug\Traits\HasSlug;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Casts\SafeContent;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Tag extends ParentModel
{
    use HasSlug;
    use HasLanguageTranslations;
    use HasMetaBoxes;

    protected $table = 'tags';

    protected $fillable = [
        'name',
        'description',
        'status',
        'author_id',
        'author_type',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $tag): void {
            $tag->posts()->detach();
        });

        static::creating(function (self $tag): void {
            $tag->author_id = $tag->author_id ?: auth()->id();
            $tag->author_type = $tag->author_type ?: User::class;
        });
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }
}
