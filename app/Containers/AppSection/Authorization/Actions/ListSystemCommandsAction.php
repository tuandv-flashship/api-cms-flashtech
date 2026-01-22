<?php

namespace App\Containers\AppSection\Authorization\Actions;

use App\Ship\Parents\Actions\Action as ParentAction;
use App\Ship\Supports\SystemCommandRegistry;

final class ListSystemCommandsAction extends ParentAction
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(): array
    {
        $items = [];

        foreach (SystemCommandRegistry::allowedActions() as $action) {
            $definition = SystemCommandRegistry::resolve($action);
            if ($definition === null) {
                continue;
            }

            $items[] = [
                'action' => $action,
                'command' => $definition['command'] ?? null,
                'options' => $definition['options'] ?? [],
            ];
        }

        return $items;
    }
}
