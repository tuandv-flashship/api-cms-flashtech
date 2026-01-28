<?php

namespace App\Containers\AppSection\Blog\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TagTranslation extends ParentModel
{
    public $timestamps = false;

    protected $table = 'tags_translations';

    protected $fillable = [
        'lang_code',
        'tags_id',
        'name',
        'description',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tags_id');
    }
}
