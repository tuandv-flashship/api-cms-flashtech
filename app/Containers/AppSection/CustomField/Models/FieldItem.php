<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FieldItem extends ParentModel
{
    public $timestamps = false;

    protected $table = 'field_items';

    protected $fillable = [
        'field_group_id',
        'parent_id',
        'order',
        'title',
        'slug',
        'type',
        'instructions',
        'options',
    ];

    protected $casts = [
        'order' => 'int',
        'parent_id' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $item): void {
            $item->customFields()->each(static fn (CustomField $field) => $field->delete());
        });
    }

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class, 'field_group_id')->withDefault();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class, 'field_item_id');
    }
}
