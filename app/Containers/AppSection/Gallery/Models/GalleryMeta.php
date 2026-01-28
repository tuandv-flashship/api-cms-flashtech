<?php

namespace App\Containers\AppSection\Gallery\Models;

use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class GalleryMeta extends ParentModel
{
    use HasLanguageTranslations;

    protected $table = 'gallery_meta';

    protected $fillable = [
        'images',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryMetaTranslation::class, 'gallery_meta_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function getImagesAttribute(mixed $value): mixed
    {
        $images = $this->getTranslatedAttribute('images', $value);
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return [];
        }

        return $images;
    }
}
