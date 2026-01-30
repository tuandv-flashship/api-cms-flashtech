<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FieldGroup extends ParentModel
{
    protected $table = 'field_groups';

    protected $fillable = [
        'title',
        'rules',
        'order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'order' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $group): void {
            $group->fieldItems()->each(static fn (FieldItem $item) => $item->delete());
        });
    }

    public function fieldItems(): HasMany
    {
        return $this->hasMany(FieldItem::class, 'field_group_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }
}
