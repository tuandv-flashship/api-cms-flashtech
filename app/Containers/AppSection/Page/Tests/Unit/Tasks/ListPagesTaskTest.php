<?php

namespace App\Containers\AppSection\Page\Tests\Unit\Tasks;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tasks\ListPagesTask;
use App\Containers\AppSection\Page\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListPagesTask::class)]
final class ListPagesTaskTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        request()->query->replace([]);

        parent::tearDown();
    }

    public function testUsesRequestCriteriaForSortingAndPagination(): void
    {
        $this->createPage(name: 'Refactor-Zulu-Unit', status: ContentStatus::PUBLISHED->value, template: 'default');
        $this->createPage(name: 'Refactor-Alpha-Unit', status: ContentStatus::PUBLISHED->value, template: 'default');

        request()->query->replace([
            'search' => 'Refactor-Alpha-Unit',
            'orderBy' => 'name',
            'sortedBy' => 'asc',
            'limit' => 1,
        ]);

        $task = app(ListPagesTask::class);
        $result = $task->run([]);

        $this->assertSame(1, $result->perPage());
        $this->assertCount(1, $result->items());
        $this->assertSame('Refactor-Alpha-Unit', $result->items()[0]->name);
    }

    public function testAppliesStatusAndTemplateFiltersFromPayload(): void
    {
        $matching = $this->createPage(
            name: 'Landing',
            status: ContentStatus::PUBLISHED->value,
            template: 'unit-template-only',
        );
        $this->createPage(name: 'Draft', status: ContentStatus::DRAFT->value, template: 'unit-template-only');
        $this->createPage(name: 'News', status: ContentStatus::PUBLISHED->value, template: 'another-template');

        request()->query->replace(['limit' => 10]);

        $task = app(ListPagesTask::class);
        $result = $task->run([
            'status' => ContentStatus::PUBLISHED->value,
            'template' => 'unit-template-only',
        ]);

        $this->assertCount(1, $result->items());
        $this->assertSame($matching->id, $result->items()[0]->id);
    }

    private function createPage(string $name, string $status, string $template): Page
    {
        return Page::query()->create([
            'name' => $name,
            'content' => 'content',
            'description' => 'description',
            'template' => $template,
            'status' => $status,
        ]);
    }
}
