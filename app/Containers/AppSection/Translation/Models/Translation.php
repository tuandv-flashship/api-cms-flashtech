<?php

namespace App\Containers\AppSection\Translation\Models;

use App\Ship\Parents\Models\Model as ParentModel;

final class Translation extends ParentModel
{
    protected $table = 'translations';

    public $timestamps = false;

    protected $guarded = [];
}
