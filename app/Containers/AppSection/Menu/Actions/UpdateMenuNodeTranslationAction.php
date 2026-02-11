<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\Menu\Events\MenuNodeTranslationUpdatedEvent;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Tasks\FindMenuNodeTask;
use App\Containers\AppSection\Menu\Tasks\UpdateMenuNodeTranslationTask;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\UpdateMenuNodeTranslationRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;

final class UpdateMenuNodeTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindMenuNodeTask $findMenuNodeTask,
        private readonly UpdateMenuNodeTranslationTask $updateMenuNodeTranslationTask,
    ) {
    }

    public function run(UpdateMenuNodeTranslationRequest $request): MenuNode
    {
        $payload = $request->validated();

        $node = $this->findMenuNodeTask->run((int) $request->id, ['menu']);

        $this->updateMenuNodeTranslationTask->run(
            $node,
            (string) $payload['lang_code'],
            Arr::only($payload, ['title', 'url']),
        );

        MenuNodeTranslationUpdatedEvent::dispatch((int) $node->menu_id, (string) $payload['lang_code']);

        return $this->findMenuNodeTask->run((int) $request->id, ['children', 'translations']);
    }
}
