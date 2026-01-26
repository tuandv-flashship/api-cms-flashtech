<?php

namespace App\Containers\AppSection\Slug\Models;

use App\Containers\AppSection\LanguageAdvanced\Models\SlugTranslation;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Slug extends ParentModel
{
    protected $table = 'slugs';

    protected $fillable = [
        'key',
        'reference_type',
        'reference_id',
        'prefix',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SlugTranslation::class, 'slugs_id');
    }
}
