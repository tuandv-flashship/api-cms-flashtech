<?php

namespace App\Containers\AppSection\Page\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PageTranslation extends ParentModel
{
    public $timestamps = false;

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
