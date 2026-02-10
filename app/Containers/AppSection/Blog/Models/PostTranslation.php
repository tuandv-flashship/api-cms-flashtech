<?php

namespace App\Containers\AppSection\Blog\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PostTranslation extends ParentModel
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'posts_translations';

    protected $fillable = [
        'lang_code',
        'posts_id',
        'name',
        'description',
        'content',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'posts_id');
    }
}
