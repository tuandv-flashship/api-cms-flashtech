<?php

namespace App\Containers\AppSection\Member\UI\API\Transformers;

use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

class MemberTransformer extends ParentTransformer
{
    protected array $defaultIncludes = [

    ];

    protected array $availableIncludes = [

    ];

    public function transform(Member $member): array
    {
        return [
            'object' => 'Member',
            'id' => $member->getHashedKey(),
            'name' => $member->name,
            'username' => $member->username,
            'email' => $member->email,
            'email_verified_at' => $member->email_verified_at,
            'dob' => $member->dob,
            'phone' => $member->phone,
            'description' => $member->description,
            'status' => $member->status,
            'created_at' => $member->created_at,
            'updated_at' => $member->updated_at,
        ];
    }
}
