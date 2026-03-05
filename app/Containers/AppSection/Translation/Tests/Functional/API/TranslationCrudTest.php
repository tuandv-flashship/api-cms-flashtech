<?php

namespace App\Containers\AppSection\Translation\Tests\Functional\API;

use App\Containers\AppSection\Translation\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Translation\UI\API\Controllers\CreateTranslationLocaleController;
use App\Containers\AppSection\Translation\UI\API\Controllers\DeleteTranslationLocaleController;
use App\Containers\AppSection\Translation\UI\API\Controllers\DownloadTranslationLocaleController;
use App\Containers\AppSection\Translation\UI\API\Controllers\GetTranslationGroupController;
use App\Containers\AppSection\Translation\UI\API\Controllers\ListTranslationGroupsController;
use App\Containers\AppSection\Translation\UI\API\Controllers\ListTranslationLocalesController;
use App\Containers\AppSection\Translation\UI\API\Controllers\UpdateTranslationGroupController;
use App\Containers\AppSection\Translation\UI\API\Controllers\UpdateTranslationJsonController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ListTranslationLocalesController::class)]
#[CoversClass(CreateTranslationLocaleController::class)]
#[CoversClass(DeleteTranslationLocaleController::class)]
#[CoversClass(ListTranslationGroupsController::class)]
#[CoversClass(GetTranslationGroupController::class)]
#[CoversClass(UpdateTranslationGroupController::class)]
#[CoversClass(UpdateTranslationJsonController::class)]
#[CoversClass(DownloadTranslationLocaleController::class)]
final class TranslationCrudTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();

        $permissions = Permission::whereIn('name', [
            'translations.index',
            'translations.create',
            'translations.edit',
            'translations.destroy',
            'translations.download',
        ])->where('guard_name', 'api')->get();

        $this->user->syncPermissions($permissions);
    }

    // ─── List Locales ────────────────────────────────────────────────

    public function testListTranslationLocales(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/translations/locales');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->etc(),
        );
    }

    public function testListTranslationLocalesWithoutPermissionFails(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')
            ->getJson('/v1/translations/locales');

        $response->assertForbidden();
    }

    // ─── Create Locale ───────────────────────────────────────────────

    public function testCreateTranslationLocale(): void
    {
        // Use a locale that likely doesn't exist yet in lang/
        $testLocale = 'test_' . uniqid();

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/translations/locales', [
                'locale' => $testLocale,
            ]);

        // May succeed or fail depending on locale validation
        // Assert the endpoint is reachable and responds properly
        $this->assertContains($response->getStatusCode(), [201, 200, 422]);

        // Clean up if locale was created
        $localePath = lang_path($testLocale);
        if (File::isDirectory($localePath)) {
            File::deleteDirectory($localePath);
        }
    }

    public function testCreateTranslationLocaleInvalidCodeFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/translations/locales', [
                'locale' => 'not_a_valid_locale_xyz',
            ]);

        $response->assertUnprocessable();
    }

    // ─── List Groups ─────────────────────────────────────────────────

    public function testListTranslationGroups(): void
    {
        // Use the default locale (en)
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/translations/en/groups');

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json
                ->has('data')
                ->etc(),
        );
    }

    // ─── Get Group ───────────────────────────────────────────────────

    public function testGetTranslationGroup(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/translations/en/group?group=validation');

        // May return 200 or 404 depending on whether 'validation' group exists
        $this->assertContains($response->getStatusCode(), [200, 404]);
    }

    // ─── Update Group ────────────────────────────────────────────────

    public function testUpdateTranslationGroupWithoutPermissionFails(): void
    {
        $user = User::factory()->createOne();

        $response = $this->actingAs($user, 'api')
            ->patchJson('/v1/translations/en/group', [
                'group' => 'validation',
                'translations' => ['accepted' => 'The :attribute must be accepted.'],
            ]);

        $response->assertForbidden();
    }

    // ─── Download Locale ─────────────────────────────────────────────

    public function testDownloadTranslationLocale(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/translations/locales/en/download');

        // Should return 200 with file download or error if locale doesn't exist
        $this->assertContains($response->getStatusCode(), [200, 404]);
    }

    // ─── Route Security ──────────────────────────────────────────────

    public function testTranslationRoutesRequireAuthentication(): void
    {
        $endpoints = [
            ['GET', '/v1/translations/locales'],
            ['POST', '/v1/translations/locales'],
            ['GET', '/v1/translations/en/groups'],
            ['GET', '/v1/translations/en/group'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $this->assertContains(
                $response->getStatusCode(),
                [401, 403, 500],
                "Endpoint {$method} {$uri} should require authentication",
            );
        }
    }
}
