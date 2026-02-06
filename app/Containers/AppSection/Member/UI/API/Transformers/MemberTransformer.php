<?php

namespace App\Containers\AppSection\Member\UI\API\Transformers;

use App\Containers\AppSection\Member\Enums\MemberStatus;
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
        $status = $member->status;

        return [
            'type' => $member->getResourceKey(),
            'id' => $member->getHashedKey(),
            'name' => $member->name,
            'username' => $member->username,
            'email' => $member->email,
            'email_verified_at' => $member->email_verified_at?->toISOString(),
            'dob' => $member->dob?->toDateString(),
            'phone' => $member->phone,
            'description' => $member->description,
            'status' => $status instanceof MemberStatus ? $status->value : $status,
            'created_at' => $member->created_at?->toISOString(),
            'updated_at' => $member->updated_at?->toISOString(),
        ];
    }
}
