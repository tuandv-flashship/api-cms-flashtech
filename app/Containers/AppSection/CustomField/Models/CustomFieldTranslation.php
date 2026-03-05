<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CustomFieldTranslation extends ParentModel
{
    /**
     * This model uses a composite primary key (lang_code + FK) defined in the migration.
     * Eloquent does not support composite PKs natively, so $primaryKey is null.
     * Do NOT use find(), save() on existing records, or refresh().
     * Use updateOrCreate() or relation queries instead.
     */
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'custom_fields_translations';

    protected $fillable = [
        'lang_code',
        'custom_fields_id',
        'value',
    ];

    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_fields_id');
    }
}
