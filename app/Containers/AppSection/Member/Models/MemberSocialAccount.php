<?php

namespace App\Containers\AppSection\Member\Models;

use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberSocialAccount extends ParentModel
{
    protected $fillable = [
        'member_id',
        'provider',
        'provider_id',
        'token',
        'avatar',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
