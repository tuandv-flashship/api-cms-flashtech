<?php

namespace App\Containers\AppSection\CustomField\Tests\Unit\UI\API\Transformers;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Tests\UnitTestCase;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(FieldGroupTransformer::class)]
#[Group('customfield')]
final class FieldGroupTransformerTest extends UnitTestCase
{
    private FieldGroupTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new FieldGroupTransformer();
    }

    public function testCanTransformFieldGroup(): void
    {
        $group = FieldGroup::factory()->create();

        $transformed = $this->transformer->transform($group);

        $this->assertEquals($group->getHashedKey(), $transformed['id']);
        $this->assertEquals($group->title, $transformed['title']);
        $this->assertEquals($group->order, $transformed['order']);
        $this->assertEquals($group->status->value, $transformed['status']);
        
        $expectedCreatedBy = config('apiato.hash-id') 
            ? hashids()->encodeOrFail($group->created_by) 
            : $group->created_by;
            
        $this->assertEquals($expectedCreatedBy, $transformed['created_by']);
    }
}
