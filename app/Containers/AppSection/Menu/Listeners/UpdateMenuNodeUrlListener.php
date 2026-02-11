<?php

namespace App\Containers\AppSection\Menu\Listeners;

use App\Containers\AppSection\Blog\Events\CategoryUpdated;
use App\Containers\AppSection\Blog\Events\PostUpdated;
use App\Containers\AppSection\Blog\Events\TagUpdated;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Supports\MenuCache;
use App\Containers\AppSection\Menu\Supports\MenuNodeResolver;
use App\Containers\AppSection\Page\Events\PageUpdatedEvent;
use Illuminate\Database\Eloquent\Model;

final class UpdateMenuNodeUrlListener
{
    public function __construct(
        private readonly MenuNodeResolver $resolver,
        private readonly MenuCache $menuCache,
    ) {
    }

    public function handle(PageUpdatedEvent|PostUpdated|CategoryUpdated|TagUpdated $event): void
    {
        $model = $this->extractModel($event);
        if ($model === null) {
            return;
        }

        $referenceType = $model::class;
        $referenceId = (int) $model->getKey();
        $resolved = $this->resolver->resolve($referenceType, $referenceId);

        $menuIds = MenuNode::query()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->distinct()
            ->pluck('menu_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($menuIds === []) {
            return;
        }

        if ($resolved['url'] !== null) {
            MenuNode::query()
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->where('url_source', 'resolved')
                ->update(['url' => $resolved['url']]);
        }

        if ($resolved['title'] !== null) {
            MenuNode::query()
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->where('title_source', 'resolved')
                ->update(['title' => $resolved['title']]);
        }

        foreach ($menuIds as $menuId) {
            $this->menuCache->forgetByMenuId($menuId);
        }
    }

    private function extractModel(PageUpdatedEvent|PostUpdated|CategoryUpdated|TagUpdated $event): ?Model
    {
        return $event->page ?? $event->post ?? $event->category ?? $event->tag ?? null;
    }
}
