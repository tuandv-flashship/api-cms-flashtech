<?php

namespace App\Containers\AppSection\Menu\Tests\Unit\UI\API\Transformers;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Tasks\BuildMenuTreeTask;
use App\Containers\AppSection\Menu\Tests\UnitTestCase;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuNodeTransformer;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuTransformer;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MenuTransformer::class)]
#[CoversClass(MenuNodeTransformer::class)]
final class MenuTransformerQueryTest extends UnitTestCase
{
    public function testTransformersDoNotIssueQueriesWhenRelationsAreLoaded(): void
    {
        $menu = Menu::query()->create([
            'name' => 'Transformer',
            'slug' => 'transformer-menu',
            'status' => 'published',
        ]);
        MenuLocation::query()->create([
            'menu_id' => $menu->id,
            'location' => 'main-menu',
        ]);

        $parent = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Parent',
            'url' => '/parent',
            'position' => 0,
        ]);
        MenuNode::query()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'title' => 'Child',
            'url' => '/child',
            'position' => 0,
        ]);

        $loadedMenu = Menu::query()
            ->with(['locations', 'nodes.translations'])
            ->findOrFail($menu->id);
        $tree = app(BuildMenuTreeTask::class)->run($loadedMenu->nodes);
        $loadedMenu->setRelation('nodes', $tree);

        $menuTransformer = new MenuTransformer();
        $nodeTransformer = new MenuNodeTransformer();
        LanguageAdvancedManager::setTranslationLocale((string) app()->getLocale());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $menuTransformer->transform($loadedMenu);
        $menuTransformer->includeLocations($loadedMenu)->getData();
        $nodes = $menuTransformer->includeNodes($loadedMenu)->getData();

        foreach ($nodes as $node) {
            $nodeTransformer->transform($node);
            $nodeTransformer->includeChildren($node)->getData();
        }

        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        DB::flushQueryLog();

        $this->assertSame(0, count($queries));
    }
}
