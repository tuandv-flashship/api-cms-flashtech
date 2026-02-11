<?php

namespace App\Containers\AppSection\Menu\Models;

use App\Containers\AppSection\LanguageAdvanced\Traits\HasLanguageTranslations;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MenuNode extends ParentModel
{
    use HasLanguageTranslations;

    protected $table = 'menu_nodes';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'reference_type',
        'reference_id',
        'url',
        'title',
        'url_source',
        'title_source',
        'icon_font',
        'css_class',
        'target',
        'has_child',
        'position',
    ];

    protected $casts = [
        'has_child' => 'boolean',
        'position' => 'integer',
        'reference_id' => 'integer',
        'menu_id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MenuNodeTranslation::class, 'menu_nodes_id');
    }

    public function getTitleAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('title', $value);
    }

    public function getUrlAttribute(mixed $value): mixed
    {
        return $this->getTranslatedAttribute('url', $value);
    }
}
