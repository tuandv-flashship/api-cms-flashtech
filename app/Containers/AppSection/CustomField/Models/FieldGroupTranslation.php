<?php

namespace App\Containers\AppSection\CustomField\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FieldGroupTranslation extends ParentModel
{
    /**
     * Composite primary key (lang_code + field_groups_id).
     * Eloquent does not support composite PKs natively.
     * Use updateOrCreate() or relation queries instead of find()/save().
     */
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'field_groups_translations';

    protected $fillable = [
        'lang_code',
        'field_groups_id',
        'title',
    ];

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class, 'field_groups_id');
    }
}
