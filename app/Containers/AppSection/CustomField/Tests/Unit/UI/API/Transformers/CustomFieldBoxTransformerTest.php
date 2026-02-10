<?php

namespace App\Containers\AppSection\CustomField\Tests\Unit\UI\API\Transformers;

use App\Containers\AppSection\CustomField\Tests\UnitTestCase;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldBoxTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(CustomFieldBoxTransformer::class)]
#[Group('customfield')]
final class CustomFieldBoxTransformerTest extends UnitTestCase
{
    private CustomFieldBoxTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new CustomFieldBoxTransformer();
    }

    public function testCanTransformCustomFieldBox(): void
    {
        $data = [
            'id' => 123,
            'title' => 'Test Box',
            'items' => [
                [
                    'id' => 456,
                    'title' => 'Item 1',
                    'type' => 'text',
                    'items' => [],
                ]
            ]
        ];

        $transformed = $this->transformer->transform($data);

        $expectedId = config('apiato.hash-id') ? hashids()->encodeOrFail(123) : 123;
        $expectedItemId = config('apiato.hash-id') ? hashids()->encodeOrFail(456) : 456;

        $this->assertEquals($expectedId, $transformed['id']);
        $this->assertEquals('Test Box', $transformed['title']);
        $this->assertCount(1, $transformed['items']);
        $this->assertEquals($expectedItemId, $transformed['items'][0]['id']);
    }
}
