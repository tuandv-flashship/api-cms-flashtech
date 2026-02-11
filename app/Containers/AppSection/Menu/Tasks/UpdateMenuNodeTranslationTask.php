<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Models\MenuNodeTranslation;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateMenuNodeTranslationTask extends ParentTask
{
    /**
     * @param array<string, mixed> $data
     */
    public function run(MenuNode $node, string $langCode, array $data): MenuNodeTranslation
    {
        $payload = [
            'menu_nodes_id' => (int) $node->getKey(),
        ];

        if (array_key_exists('title', $data)) {
            $payload['title'] = $data['title'];
        }

        if (array_key_exists('url', $data)) {
            $payload['url'] = $data['url'];
        }

        return $node->translations()->updateOrCreate(
            ['lang_code' => $langCode],
            $payload,
        );
    }
}
