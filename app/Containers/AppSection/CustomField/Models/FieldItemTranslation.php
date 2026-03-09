<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FieldItemTranslation extends ParentModel
{
    /**
     * Composite primary key (lang_code + field_items_id).
     * Eloquent does not support composite PKs natively.
     * Use updateOrCreate() or relation queries instead of find()/save().
     */
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'field_items_translations';

    protected $fillable = [
        'lang_code',
        'field_items_id',
        'title',
        'instructions',
        'options',
    ];

    public function fieldItem(): BelongsTo
    {
        return $this->belongsTo(FieldItem::class, 'field_items_id');
    }
}
