<?php

namespace App\Containers\AppSection\Page\Tests\Unit\Tasks;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tasks\FindPageTask;
use App\Containers\AppSection\Page\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FindPageTask::class)]
final class FindPageTaskTest extends UnitTestCase
{
    public function testEagerLoadsIncludedUserRelationWhenRequested(): void
    {
        $user = User::factory()->createOne();
        $page = $this->createPage(userId: $user->id);

        $task = app(FindPageTask::class);
        $found = $task->run($page->id, 'user');

        $this->assertTrue($found->relationLoaded('user'));
        $this->assertSame($user->id, $found->user?->id);
    }

    public function testKeepsUserRelationLazyWhenNotIncluded(): void
    {
        $user = User::factory()->createOne();
        $page = $this->createPage(userId: $user->id);

        $task = app(FindPageTask::class);
        $found = $task->run($page->id, null);

        $this->assertFalse($found->relationLoaded('user'));
    }

    private function createPage(int $userId): Page
    {
        return Page::query()->create([
            'name' => 'Page Name',
            'content' => 'content',
            'description' => 'description',
            'template' => 'default',
            'status' => ContentStatus::PUBLISHED->value,
            'user_id' => $userId,
        ]);
    }
}
