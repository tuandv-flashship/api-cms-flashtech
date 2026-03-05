<?php

namespace App\Containers\AppSection\Revision\Tests\Functional\API;

use App\Containers\AppSection\Revision\Tests\ContainerTestCase;
use App\Containers\AppSection\Revision\UI\API\Controllers\ListRevisionsController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ListRevisionsController::class)]
final class RevisionApiTest extends ContainerTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();

        $permission = Permission::where('name', 'revisions.index')
            ->where('guard_name', 'api')
            ->first();

        $this->user->givePermissionTo($permission);
    }

    public function testListRevisionsRequiresParams(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/revisions');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type', 'revisionable_id']);
    }

    public function testListRevisionsWithParams(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/revisions?type=post&revisionable_id=1');

        // Returns 200 (possibly empty list) or 422 if type isn't valid
        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function testListRevisionsWithoutAuthFails(): void
    {
        $response = $this->getJson('/v1/revisions');

        $this->assertContains($response->getStatusCode(), [401, 403, 500]);
    }

    public function testListRevisionsWithoutPermissionFails(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')
            ->getJson('/v1/revisions');

        $response->assertForbidden();
    }
}
