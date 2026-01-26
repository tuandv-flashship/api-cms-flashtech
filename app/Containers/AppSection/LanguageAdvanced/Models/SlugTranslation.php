<?php

namespace App\Containers\AppSection\LanguageAdvanced\Models;

use App\Containers\AppSection\Slug\Models\Slug;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SlugTranslation extends ParentModel
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'slugs_translations';

    protected $fillable = [
        'lang_code',
        'slugs_id',
        'key',
        'prefix',
    ];

    public function slug(): BelongsTo
    {
        return $this->belongsTo(Slug::class, 'slugs_id');
    }
}
