<?php

namespace App\Containers\AppSection\MetaBox\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class MetaBox extends ParentModel
{
    protected $table = 'meta_boxes';

    protected $fillable = [
        'meta_key',
        'meta_value',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'meta_value' => 'json',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
