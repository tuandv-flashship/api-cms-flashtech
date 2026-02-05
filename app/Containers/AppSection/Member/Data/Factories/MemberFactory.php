<?php

namespace App\Containers\AppSection\Member\Data\Factories;

use App\Containers\AppSection\Member\Enums\MemberStatus;
use App\Containers\AppSection\Member\Models\Member;
use App\Ship\Parents\Factories\Factory as ParentFactory;
use Illuminate\Support\Facades\Hash;

class MemberFactory extends ParentFactory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'dob' => $this->faker->date(),
            'phone' => $this->faker->phoneNumber(),
            'description' => $this->faker->sentence(),
            'status' => MemberStatus::ACTIVE,
        ];
    }
}
