<?php

namespace App\Containers\AppSection\Gallery\Tests\Unit\UI\API\Transformers;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\Gallery\Tests\UnitTestCase;
use App\Containers\AppSection\Gallery\UI\API\Transformers\GalleryTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(GalleryTransformer::class)]
#[Group('gallery')]
final class GalleryTransformerTest extends UnitTestCase
{
    private GalleryTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new GalleryTransformer();
    }

    public function testCanTransformSingleObject(): void
    {
        $gallery = Gallery::factory()->create();

        $transformed = $this->transformer->transform($gallery);

        $this->assertEquals($gallery->getHashedKey(), $transformed['id']);
        $this->assertEquals($gallery->name, $transformed['name']);
        $this->assertEquals((bool)$gallery->is_featured, $transformed['is_featured']);
        $this->assertEquals($gallery->status->value, $transformed['status']);
        
        // Verify author_id is handled by hashId trait correctly
        $expectedAuthorId = config('apiato.hash-id') 
            ? hashids()->encodeOrFail($gallery->author_id) 
            : $gallery->author_id;

        $this->assertEquals($expectedAuthorId, $transformed['author_id']);
    }
}
