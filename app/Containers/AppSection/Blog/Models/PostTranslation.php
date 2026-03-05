<?php

namespace App\Containers\AppSection\Blog\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PostTranslation extends ParentModel
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
