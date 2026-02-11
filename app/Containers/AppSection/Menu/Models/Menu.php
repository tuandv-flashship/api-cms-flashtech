<?php

namespace App\Containers\AppSection\Menu\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Menu extends ParentModel
{
    protected $table = 'menus';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(MenuLocation::class, 'menu_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(MenuNode::class, 'menu_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
