<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Ship\Parents\Actions\Action as ParentAction;

final class GetMenuOptionsAction extends ParentAction
{
    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public function run(): array
    {
        $locations = [];
        foreach ((array) config('menu.locations', []) as $key => $label) {
            if (! is_string($key) || ! is_string($label)) {
                continue;
            }

            $locations[] = [
                'key' => $key,
                'label' => $label,
            ];
        }

        $referenceTypes = [];
        foreach ((array) config('menu.reference_types', []) as $key => $class) {
            if (! is_string($key) || ! is_string($class)) {
                continue;
            }

            $referenceTypes[] = [
                'key' => $key,
                'class' => $class,
            ];
        }

        return [
            'locations' => $locations,
            'reference_types' => $referenceTypes,
        ];
    }
}
