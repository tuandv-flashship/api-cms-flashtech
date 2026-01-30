<?php

namespace App\Containers\AppSection\Page\Models;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Containers\AppSection\MetaBox\Traits\HasMetaBoxes;
use App\Containers\AppSection\Revision\Traits\RevisionableTrait;
use App\Containers\AppSection\Slug\Traits\HasSlug;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Casts\SafeContent;
use App\Ship\Casts\SafeContentCms;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Page extends ParentModel
{
    use HasSlug;
    use HasLanguageTranslations;
    use HasMetaBoxes;
    use RevisionableTrait;

    protected $table = 'pages';

    protected bool $revisionEnabled = true;
    protected bool $revisionCleanup = true;
    protected int $historyLimit = 20;
    protected array $dontKeepRevisionOf = [
        'content',
    ];

    protected $fillable = [
        'name',
        'content',
        'image',
        'template',
        'description',
        'status',
        'user_id',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'content' => SafeContentCms::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $page): void {
            $page->user_id = $page->user_id ?: auth()->id();
        });

        static::deleted(function (self $page): void {
            CustomField::query()
                ->where('use_for', self::class)
                ->where('use_for_id', $page->getKey())
                ->each(static fn (CustomField $field) => $field->delete());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'pages_id');
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
