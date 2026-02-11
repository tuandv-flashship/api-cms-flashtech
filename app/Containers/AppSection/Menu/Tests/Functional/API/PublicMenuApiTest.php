<?php

namespace App\Containers\AppSection\Menu\Tests\Functional\API;

use App\Containers\AppSection\Blog\Events\PostDeleted;
use App\Containers\AppSection\Blog\Events\PostUpdated;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\Models\MenuNodeTranslation;
use App\Containers\AppSection\Menu\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Menu\UI\API\Controllers\GetMenuByLocationController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GetMenuByLocationController::class)]
final class PublicMenuApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testGetMenuByLocationReturnsTree(): void
    {
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-public', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);

        $parent = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Parent',
            'url' => '/parent',
            'position' => 0,
            'has_child' => true,
        ]);

        MenuNode::query()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'title' => 'Child',
            'url' => '/child',
            'position' => 0,
            'has_child' => false,
        ]);

        $response = $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'main-menu']));

        $response->assertOk();
        $response->assertJson(static fn (AssertableJson $json): AssertableJson => $json
            ->where('data.slug', 'main-public')
            ->has('data.nodes.data.0')
            ->where('data.nodes.data.0.title', 'Parent')
            ->where('data.nodes.data.0.children.data.0.title', 'Child')
            ->etc());
    }

    public function testGetMenuByLocationUsesAndInvalidatesCache(): void
    {
        $menu = Menu::query()->create(['name' => 'Cache', 'slug' => 'cache-menu', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'footer']);

        MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Before',
            'url' => '/before',
            'position' => 0,
        ]);

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'footer']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Before');

        MenuNode::query()->where('menu_id', $menu->id)->update(['title' => 'After']);

        $cached = $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'footer']));
        $cached->assertOk()->assertJsonPath('data.nodes.data.0.title', 'Before');

        event(new \App\Containers\AppSection\Menu\Events\MenuSavedEvent($menu->id));

        $fresh = $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'footer']));
        $fresh->assertOk()->assertJsonPath('data.nodes.data.0.title', 'After');
    }

    public function testGetMenuByLocationUsesLocaleParamForTranslation(): void
    {
        $langCode = $this->nonDefaultLanguageCode();
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-translation', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);

        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/home',
            'position' => 0,
        ]);

        MenuNodeTranslation::query()->create([
            'menu_nodes_id' => $node->id,
            'lang_code' => $langCode,
            'title' => 'Trang chủ',
        ]);

        $response = $this->getJson(URL::action(GetMenuByLocationController::class, [
            'location' => 'main-menu',
            'lang_code' => $langCode,
        ]));

        $response->assertOk()->assertJsonPath('data.nodes.data.0.title', 'Trang chủ');
    }

    public function testMenuDeletedEventInvalidatesCachedLocation(): void
    {
        $menu = Menu::query()->create(['name' => 'Footer', 'slug' => 'footer-cache-delete', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'footer']);

        MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Before Delete',
            'url' => '/before-delete',
            'position' => 0,
        ]);

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'footer']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Before Delete');

        Menu::query()->whereKey($menu->id)->delete();

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'footer']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Before Delete');

        event(new \App\Containers\AppSection\Menu\Events\MenuDeletedEvent($menu->id, ['footer']));

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'footer']))
            ->assertNotFound();
    }

    public function testTranslationUpdatedEventInvalidatesLocaleCache(): void
    {
        $langCode = $this->nonDefaultLanguageCode();
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-cache-translation', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);

        $node = MenuNode::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/home',
            'position' => 0,
        ]);

        $this->getJson(URL::action(GetMenuByLocationController::class, [
            'location' => 'main-menu',
            'lang_code' => $langCode,
        ]))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Home');

        MenuNodeTranslation::query()->updateOrCreate(
            ['menu_nodes_id' => $node->id, 'lang_code' => $langCode],
            ['title' => 'Trang chủ mới'],
        );

        $this->getJson(URL::action(GetMenuByLocationController::class, [
            'location' => 'main-menu',
            'lang_code' => $langCode,
        ]))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Home');

        event(new \App\Containers\AppSection\Menu\Events\MenuNodeTranslationUpdatedEvent($menu->id, $langCode));

        $this->getJson(URL::action(GetMenuByLocationController::class, [
            'location' => 'main-menu',
            'lang_code' => $langCode,
        ]))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Trang chủ mới');
    }

    public function testPostUpdatedEventInvalidatesCacheAndSyncsResolvedTitle(): void
    {
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-post-update', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);

        $post = Post::factory()->createOne(['name' => 'Post Title Before']);
        MenuNode::query()->create([
            'menu_id' => $menu->id,
            'reference_type' => Post::class,
            'reference_id' => $post->id,
            'title' => 'Legacy Title',
            'url' => '/legacy',
            'title_source' => 'resolved',
            'url_source' => 'custom',
            'position' => 0,
        ]);

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'main-menu']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Legacy Title');

        $post->update(['name' => 'Post Title After']);

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'main-menu']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Legacy Title');

        event(new PostUpdated($post->fresh()));

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'main-menu']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Post Title After');
    }

    public function testPostDeletedEventInvalidatesCacheAndRemovesResolvedNodes(): void
    {
        $menu = Menu::query()->create(['name' => 'Main', 'slug' => 'main-post-delete', 'status' => 'published']);
        MenuLocation::query()->create(['menu_id' => $menu->id, 'location' => 'main-menu']);

        $post = Post::factory()->createOne(['name' => 'Delete Me']);
        MenuNode::query()->create([
            'menu_id' => $menu->id,
            'reference_type' => Post::class,
            'reference_id' => $post->id,
            'title' => 'Will Be Deleted',
            'url' => '/will-be-deleted',
            'title_source' => 'resolved',
            'url_source' => 'resolved',
            'position' => 0,
        ]);

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'main-menu']))
            ->assertOk()
            ->assertJsonPath('data.nodes.data.0.title', 'Will Be Deleted');

        event(new PostDeleted($post->id, (string) $post->name));

        $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'main-menu']))
            ->assertOk()
            ->assertJsonCount(0, 'data.nodes.data');
    }

    public function testGetMenuByInvalidLocationReturnsValidationError(): void
    {
        $response = $this->getJson(URL::action(GetMenuByLocationController::class, ['location' => 'invalid-location']));

        $response->assertUnprocessable();
    }

    private function nonDefaultLanguageCode(): string
    {
        $defaultCode = (string) Language::query()
            ->where('lang_is_default', true)
            ->value('lang_code');

        return $defaultCode === 'vi' ? 'en' : 'vi';
    }
}
