<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CustomFieldTranslation extends ParentModel
{
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
