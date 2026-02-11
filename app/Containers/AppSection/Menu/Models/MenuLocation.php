<?php

namespace App\Containers\AppSection\Menu\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MenuLocation extends ParentModel
{
    protected $table = 'menu_locations';

    protected $fillable = [
        'menu_id',
        'location',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
