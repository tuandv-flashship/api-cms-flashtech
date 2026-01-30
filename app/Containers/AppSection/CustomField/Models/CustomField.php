<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CustomField extends ParentModel
{
    public $timestamps = false;

    protected $table = 'custom_fields';

    protected $fillable = [
        'use_for',
        'use_for_id',
        'field_item_id',
        'type',
        'slug',
        'value',
    ];

    protected $casts = [
        'use_for_id' => 'int',
        'field_item_id' => 'int',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $customField): void {
            $customField->translations()->delete();
        });
    }

    public function fieldItem(): BelongsTo
    {
        return $this->belongsTo(FieldItem::class, 'field_item_id')->withDefault();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CustomFieldTranslation::class, 'custom_fields_id');
    }

    protected function resolvedValue(): Attribute
    {
        return Attribute::get(function () {
            if ($this->type !== 'repeater') {
                return $this->value;
            }

            $decoded = json_decode((string) $this->value, true);

            return is_array($decoded) ? $decoded : null;
        });
    }
}
