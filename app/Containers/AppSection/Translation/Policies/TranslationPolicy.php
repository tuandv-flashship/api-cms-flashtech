<?php

namespace App\Containers\AppSection\Translation\Policies;

use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Policies\Policy as ParentPolicy;

final class TranslationPolicy extends ParentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('translations.index');
    }

    public function create(User $user): bool
    {
        return $user->can('translations.create');
    }

    public function update(User $user): bool
    {
        return $user->can('translations.edit');
    }

    public function delete(User $user): bool
    {
        return $user->can('translations.destroy');
    }

    public function download(User $user): bool
    {
        return $user->can('translations.download');
    }
}
