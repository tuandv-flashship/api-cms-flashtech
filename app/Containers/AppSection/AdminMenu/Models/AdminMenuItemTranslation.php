<?php

namespace App\Containers\AppSection\AdminMenu\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AdminMenuItemTranslation extends ParentModel
{
    public $incrementing = false;

    protected $primaryKey = null;

    protected $table = 'admin_menu_items_translations';

    protected $fillable = [
        'lang_code',
        'admin_menu_items_id',
        'name',
        'description',
    ];

    public function adminMenuItem(): BelongsTo
    {
        return $this->belongsTo(AdminMenuItem::class, 'admin_menu_items_id');
    }
}
