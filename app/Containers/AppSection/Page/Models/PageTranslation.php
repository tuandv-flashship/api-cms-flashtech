<?php

namespace App\Containers\AppSection\Page\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PageTranslation extends ParentModel
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

    protected $table = 'pages_translations';

    protected $fillable = [
        'lang_code',
        'pages_id',
        'name',
        'description',
        'content',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'pages_id');
    }
}
