<?php

namespace App\Containers\AppSection\Gallery\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GalleryTranslation extends ParentModel
{
    public $timestamps = false;

    protected $table = 'galleries_translations';

    protected $fillable = [
        'lang_code',
        'galleries_id',
        'name',
        'description',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'galleries_id');
    }
}
