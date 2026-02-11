<?php

namespace App\Containers\AppSection\Menu\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MenuNodeTranslation extends ParentModel
{
    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'menu_nodes_translations';

    protected $fillable = [
        'lang_code',
        'menu_nodes_id',
        'title',
        'url',
    ];

    public function menuNode(): BelongsTo
    {
        return $this->belongsTo(MenuNode::class, 'menu_nodes_id');
    }
}
