<?php

namespace App\Containers\AppSection\Gallery\Models;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Containers\AppSection\MetaBox\Traits\HasMetaBoxes;
use App\Containers\AppSection\Slug\Traits\HasSlug;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Gallery extends ParentModel
{
    use HasSlug;
    use HasLanguageTranslations;
    use HasMetaBoxes;

    protected $table = 'galleries';

    protected $fillable = [
        'name',
        'description',
        'is_featured',
        'order',
        'image',
        'status',
        'author_id',
        'author_type',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'is_featured' => 'bool',
        'order' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $gallery): void {
            GalleryMeta::query()
                ->where('reference_id', $gallery->getKey())
                ->where('reference_type', self::class)
                ->delete();
        });

        static::creating(function (self $gallery): void {
            $gallery->author_id = $gallery->author_id ?: auth()->id();
            $gallery->author_type = $gallery->author_type ?: User::class;
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryTranslation::class, 'galleries_id');
    }

    public function meta(): HasOne
    {
        return $this->hasOne(GalleryMeta::class, 'reference_id')
            ->where('reference_type', self::class);
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
}
