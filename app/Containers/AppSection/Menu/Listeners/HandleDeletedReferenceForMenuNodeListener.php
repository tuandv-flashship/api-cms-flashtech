<?php

namespace App\Containers\AppSection\Menu\Listeners;

use App\Containers\AppSection\Blog\Events\CategoryDeleted;
use App\Containers\AppSection\Blog\Events\PostDeleted;
use App\Containers\AppSection\Blog\Events\TagDeleted;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Supports\MenuCache;
use App\Containers\AppSection\Menu\Tasks\RefreshMenuNodeHasChildTask;
use App\Containers\AppSection\Page\Events\PageDeletedEvent;

final class HandleDeletedReferenceForMenuNodeListener
{
    public function __construct(
        private readonly MenuCache $menuCache,
        private readonly RefreshMenuNodeHasChildTask $refreshMenuNodeHasChildTask,
    ) {
    }

    public function handle(PageDeletedEvent|PostDeleted|CategoryDeleted|TagDeleted $event): void
    {
        $reference = $this->extractReference($event);
        if ($reference === null) {
            return;
        }

        $menuIds = MenuNode::query()
            ->where('reference_type', $reference['type'])
            ->where('reference_id', $reference['id'])
            ->distinct()
            ->pluck('menu_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($menuIds === []) {
            return;
        }

        MenuNode::query()
            ->where('reference_type', $reference['type'])
            ->where('reference_id', $reference['id'])
            ->where('url_source', 'resolved')
            ->where('title_source', 'resolved')
            ->delete();

        MenuNode::query()
            ->where('reference_type', $reference['type'])
            ->where('reference_id', $reference['id'])
            ->where(function ($query): void {
                $query->where('url_source', '!=', 'resolved')
                    ->orWhere('title_source', '!=', 'resolved');
            })
            ->update([
                'reference_type' => null,
                'reference_id' => null,
            ]);

        $this->refreshMenuNodeHasChildTask->runForMenuIds($menuIds);

        foreach ($menuIds as $menuId) {
            $this->menuCache->forgetByMenuId($menuId);
        }
    }

    /**
     * @return array{type: string, id: int}|null
     */
    private function extractReference(PageDeletedEvent|PostDeleted|CategoryDeleted|TagDeleted $event): ?array
    {
        if ($event instanceof PageDeletedEvent) {
            if (! isset($event->pageData['id'])) {
                return null;
            }

            return [
                'type' => \App\Containers\AppSection\Page\Models\Page::class,
                'id' => (int) $event->pageData['id'],
            ];
        }

        if ($event instanceof PostDeleted) {
            return [
                'type' => \App\Containers\AppSection\Blog\Models\Post::class,
                'id' => (int) $event->postId,
            ];
        }

        if ($event instanceof CategoryDeleted) {
            return [
                'type' => \App\Containers\AppSection\Blog\Models\Category::class,
                'id' => (int) $event->categoryId,
            ];
        }

        return [
            'type' => \App\Containers\AppSection\Blog\Models\Tag::class,
            'id' => (int) $event->tagId,
        ];
    }
}
