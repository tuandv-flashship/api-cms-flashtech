<?php

namespace App\Containers\AppSection\AdminMenu\Models;

use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AdminMenuItem extends ParentModel
{
    use HasLanguageTranslations;
    use SoftDeletes;

    protected $table = 'admin_menu_items';

    protected $fillable = [
        'parent_id',
        'key',
        'name',
        'icon',
        'route',
        'permissions',
        'children_display',
        'section',
        'description',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'parent_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('priority');
    }

    /**
     * All translations (not constrained by locale).
     * Use this for ?include=translations to return every locale at once.
     */
    public function allTranslations(): HasMany
    {
        return $this->hasMany(AdminMenuItemTranslation::class, 'admin_menu_items_id');
    }
}
