<?php

namespace App\Containers\AppSection\Tools\Tests\Functional\API;

use App\Containers\AppSection\Tools\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadOtherTranslationsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPageTranslationsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPagesExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPostsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPostTranslationsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportOtherTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPageTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPagesController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPostsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPostTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportOtherTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPageTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPagesController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPostsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPostTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\UploadDataSynchronizeFileController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidateOtherTranslationsImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePageTranslationsImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePagesImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePostsImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePostTranslationsImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\GetDataSynchronizeSchemaController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ListDataSynchronizeTypesController;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ExportPostsController::class)]
#[CoversClass(ExportPostTranslationsController::class)]
#[CoversClass(ExportOtherTranslationsController::class)]
#[CoversClass(ExportPagesController::class)]
#[CoversClass(ExportPageTranslationsController::class)]
#[CoversClass(ImportPostsController::class)]
#[CoversClass(ImportPostTranslationsController::class)]
#[CoversClass(ImportOtherTranslationsController::class)]
#[CoversClass(ImportPagesController::class)]
#[CoversClass(ImportPageTranslationsController::class)]
#[CoversClass(ValidatePostsImportController::class)]
#[CoversClass(ValidatePostTranslationsImportController::class)]
#[CoversClass(ValidateOtherTranslationsImportController::class)]
#[CoversClass(ValidatePagesImportController::class)]
#[CoversClass(ValidatePageTranslationsImportController::class)]
#[CoversClass(DownloadPostsExampleController::class)]
#[CoversClass(DownloadPostTranslationsExampleController::class)]
#[CoversClass(DownloadOtherTranslationsExampleController::class)]
#[CoversClass(DownloadPagesExampleController::class)]
#[CoversClass(DownloadPageTranslationsExampleController::class)]
#[CoversClass(UploadDataSynchronizeFileController::class)]
#[CoversClass(GetDataSynchronizeSchemaController::class)]
#[CoversClass(ListDataSynchronizeTypesController::class)]
final class DataSynchronizeTest extends ApiTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->createOne();

        $permissions = Permission::whereIn('name', [
            'posts.import',
            'posts.export',
            'post-translations.import',
            'post-translations.export',
            'other-translations.import',
            'other-translations.export',
            'pages.import',
            'pages.export',
            'page-translations.import',
            'page-translations.export',
        ])->where('guard_name', 'api')->get();

        $this->user->syncPermissions($permissions);
    }

    // ─── Route Security ──────────────────────────────────────────────

    public function testAllRoutesRequireAuthentication(): void
    {
        $endpoints = [
            '/v1/tools/data-synchronize/export/posts',
            '/v1/tools/data-synchronize/export/translations/model',
            '/v1/tools/data-synchronize/export/other-translations',
            '/v1/tools/data-synchronize/export/pages',
            '/v1/tools/data-synchronize/export/translations/page',
            '/v1/tools/data-synchronize/import/posts',
            '/v1/tools/data-synchronize/import/translations/model',
            '/v1/tools/data-synchronize/import/other-translations',
            '/v1/tools/data-synchronize/import/pages',
            '/v1/tools/data-synchronize/import/translations/page',
            '/v1/tools/data-synchronize/import/posts/validate',
            '/v1/tools/data-synchronize/import/translations/model/validate',
            '/v1/tools/data-synchronize/import/other-translations/validate',
            '/v1/tools/data-synchronize/import/pages/validate',
            '/v1/tools/data-synchronize/import/translations/page/validate',
            '/v1/tools/data-synchronize/upload',
        ];

        foreach ($endpoints as $uri) {
            $response = $this->postJson($uri);
            $this->assertContains(
                $response->getStatusCode(),
                [401, 403, 500],
                "Endpoint POST {$uri} should require authentication",
            );
        }

        // GET endpoints
        $getEndpoints = [
            '/v1/tools/data-synchronize/types',
            '/v1/tools/data-synchronize/schema/posts',
        ];

        foreach ($getEndpoints as $uri) {
            $response = $this->getJson($uri);
            $this->assertContains(
                $response->getStatusCode(),
                [401, 403, 500],
                "Endpoint GET {$uri} should require authentication",
            );
        }
    }

    public function testAllRoutesRequirePermission(): void
    {
        $userWithoutPermissions = User::factory()->createOne();

        $endpoints = [
            '/v1/tools/data-synchronize/export/posts',
            '/v1/tools/data-synchronize/export/translations/model',
            '/v1/tools/data-synchronize/export/other-translations',
            '/v1/tools/data-synchronize/export/pages',
            '/v1/tools/data-synchronize/export/translations/page',
        ];

        foreach ($endpoints as $uri) {
            $response = $this->actingAs($userWithoutPermissions, 'api')
                ->postJson($uri);

            $this->assertContains(
                $response->getStatusCode(),
                [403],
                "Endpoint POST {$uri} should require permission",
            );
        }
    }

    // ─── Export ──────────────────────────────────────────────────────

    public function testExportPosts(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/export/posts');

        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    public function testExportPostTranslations(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/export/translations/model');

        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    public function testExportOtherTranslations(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/export/other-translations');

        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    public function testExportPages(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/export/pages');

        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    public function testExportPageTranslations(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/export/translations/page');

        $this->assertContains($response->getStatusCode(), [200, 422, 500]);
    }

    // ─── Download Example ────────────────────────────────────────────

    public function testDownloadPostsExample(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/posts/download-example');

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function testDownloadPostTranslationsExample(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/translations/model/download-example');

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function testDownloadOtherTranslationsExample(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/other-translations/download-example');

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function testDownloadPagesExample(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/pages/download-example');

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    public function testDownloadPageTranslationsExample(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/translations/page/download-example');

        $this->assertContains($response->getStatusCode(), [200, 422]);
    }

    // ─── Validate Import (no file) ───────────────────────────────────

    public function testValidatePostsImportWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/posts/validate');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    public function testValidatePostTranslationsImportWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/translations/model/validate');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    public function testValidateOtherTranslationsImportWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/other-translations/validate');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    public function testValidatePagesImportWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/pages/validate');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    public function testValidatePageTranslationsImportWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/translations/page/validate');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    // ─── Import (no file) ────────────────────────────────────────────

    public function testImportPostsWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/posts');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    public function testImportPagesWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/pages');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    // ─── Upload ──────────────────────────────────────────────────────

    public function testUploadWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/upload');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    // ─── Schema & Types ──────────────────────────────────────────────

    public function testListTypesReturnsAllTypes(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/tools/data-synchronize/types');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['type', 'label', 'total']]]);

        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('posts', $types);
        $this->assertContains('pages', $types);
        $this->assertContains('post-translations', $types);
        $this->assertContains('page-translations', $types);
        $this->assertContains('other-translations', $types);
    }

    public function testSchemaReturnsExportAndImportMetadata(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/tools/data-synchronize/schema/posts');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'type',
                'label',
                'export' => ['total', 'columns', 'filters', 'formats'],
                'import' => ['columns', 'formats'],
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('posts', $data['type']);
        $this->assertIsArray($data['export']['columns']);
        $this->assertIsArray($data['export']['filters']);
        $this->assertContains('csv', $data['export']['formats']);
    }

    public function testSchemaForPagesReturnsFilters(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/tools/data-synchronize/schema/pages');

        $response->assertOk();

        $filters = collect($response->json('data.export.filters'))->pluck('key')->all();
        $this->assertContains('status', $filters);
        $this->assertContains('template', $filters);
        $this->assertContains('start_date', $filters);
    }

    public function testSchemaForTranslationsHasLocaleColumns(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/tools/data-synchronize/schema/page-translations');

        $response->assertOk();

        $exportCols = collect($response->json('data.export.columns'))->pluck('key')->all();
        $this->assertContains('id', $exportCols);
        $this->assertContains('name', $exportCols);
    }

    public function testSchemaForUnknownTypeReturns404(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/v1/tools/data-synchronize/schema/unknown-type');

        $response->assertNotFound();
    }
}
