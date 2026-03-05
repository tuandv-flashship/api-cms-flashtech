<?php

namespace App\Containers\AppSection\Slug\Tests\Functional\API;

use App\Containers\AppSection\Slug\Tests\ContainerTestCase;
use App\Containers\AppSection\Slug\UI\API\Controllers\CreateSlugController;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateSlugController::class)]
final class SlugApiTest extends ContainerTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();
    }

    public function testCreateSlug(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/slugs/create', [
                'value' => 'Hello World',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function testCreateSlugWithoutNameFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/slugs/create', []);

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    public function testCreateSlugWithoutAuthFails(): void
    {
        $response = $this->postJson('/v1/slugs/create', [
            'value' => 'Hello World',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 403, 500]);
    }
}
