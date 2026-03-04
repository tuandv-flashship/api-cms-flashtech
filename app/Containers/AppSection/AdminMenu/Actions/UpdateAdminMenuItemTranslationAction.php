<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tasks\UpdateAdminMenuItemTranslationTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\UpdateAdminMenuItemTranslationRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;

final class UpdateAdminMenuItemTranslationAction extends ParentAction
{
    public function __construct(
        private readonly UpdateAdminMenuItemTranslationTask $translationTask,
        private readonly AdminMenu $adminMenu,
    ) {
    }

    public function run(UpdateAdminMenuItemTranslationRequest $request): AdminMenuItem
    {
        $validated = $request->validated();
        $item = $this->translationTask->run(
            (int) $request->id,
            $validated['lang_code'],
            $validated,
        );

        $this->adminMenu->flush();

        return $item;
    }
}
