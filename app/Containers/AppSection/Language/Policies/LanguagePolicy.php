<?php

namespace App\Containers\AppSection\Language\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class LanguagePolicy extends ParentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('languages.index');
    }

    public function create(User $user): bool
    {
        return $user->can('languages.create');
    }

    public function update(User $user): bool
    {
        return $user->can('languages.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('languages.destroy');
    }
}
