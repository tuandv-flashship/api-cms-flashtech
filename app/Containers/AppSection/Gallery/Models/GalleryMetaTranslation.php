<?php

namespace App\Containers\AppSection\Gallery\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GalleryMetaTranslation extends ParentModel
{
    public $timestamps = false;

    protected $table = 'gallery_meta_translations';

    protected $fillable = [
        'lang_code',
        'gallery_meta_id',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function meta(): BelongsTo
    {
        return $this->belongsTo(GalleryMeta::class, 'gallery_meta_id');
    }
}
