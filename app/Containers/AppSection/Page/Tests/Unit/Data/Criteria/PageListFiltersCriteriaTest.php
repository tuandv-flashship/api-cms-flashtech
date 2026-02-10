<?php

namespace App\Containers\AppSection\Page\Tests\Unit\Data\Criteria;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Data\Criteria\PageListFiltersCriteria;
use App\Containers\AppSection\Page\Data\Repositories\PageRepository;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageListFiltersCriteria::class)]
final class PageListFiltersCriteriaTest extends UnitTestCase
{
    public function testCanFilterByStatusAndTemplate(): void
    {
        $matching = $this->createPage(
            name: 'Landing Page',
            status: ContentStatus::PUBLISHED->value,
            template: 'default',
        );
        $this->createPage(name: 'Draft Page', status: ContentStatus::DRAFT->value, template: 'default');
        $this->createPage(name: 'News Page', status: ContentStatus::PUBLISHED->value, template: 'news');

        $repository = app(PageRepository::class);
        $repository->pushCriteria(new PageListFiltersCriteria([
            'status' => ContentStatus::PUBLISHED->value,
            'template' => 'default',
        ]));

        $result = $repository->all();

        $this->assertCount(1, $result);
        $this->assertSame($matching->id, $result->first()?->id);
    }

    public function testReturnsAllRowsWhenFiltersAreMissing(): void
    {
        $first = $this->createPage(name: 'Page 1', status: ContentStatus::PUBLISHED->value, template: 'default');
        $second = $this->createPage(name: 'Page 2', status: ContentStatus::DRAFT->value, template: 'news');

        $repository = app(PageRepository::class);
        $repository->pushCriteria(new PageListFiltersCriteria([]));

        $result = $repository->all();

        $this->assertGreaterThanOrEqual(2, $result->count());
        $this->assertTrue($result->contains('id', $first->id));
        $this->assertTrue($result->contains('id', $second->id));
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
