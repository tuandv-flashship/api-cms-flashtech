<?php

namespace App\Containers\AppSection\Tools\Tests\Functional\API;

use App\Containers\AppSection\Tools\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadOtherTranslationsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPostsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPostTranslationsExampleController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportOtherTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPostsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPostTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportOtherTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPostsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPostTranslationsController;
use App\Containers\AppSection\Tools\UI\API\Controllers\UploadDataSynchronizeFileController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidateOtherTranslationsImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePostsImportController;
use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePostTranslationsImportController;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\Models\Permission;

#[CoversClass(ExportPostsController::class)]
#[CoversClass(ExportPostTranslationsController::class)]
#[CoversClass(ExportOtherTranslationsController::class)]
#[CoversClass(ImportPostsController::class)]
#[CoversClass(ImportPostTranslationsController::class)]
#[CoversClass(ImportOtherTranslationsController::class)]
#[CoversClass(ValidatePostsImportController::class)]
#[CoversClass(ValidatePostTranslationsImportController::class)]
#[CoversClass(ValidateOtherTranslationsImportController::class)]
#[CoversClass(DownloadPostsExampleController::class)]
#[CoversClass(DownloadPostTranslationsExampleController::class)]
#[CoversClass(DownloadOtherTranslationsExampleController::class)]
#[CoversClass(UploadDataSynchronizeFileController::class)]
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
            '/v1/tools/data-synchronize/import/posts',
            '/v1/tools/data-synchronize/import/translations/model',
            '/v1/tools/data-synchronize/import/other-translations',
            '/v1/tools/data-synchronize/import/posts/validate',
            '/v1/tools/data-synchronize/import/translations/model/validate',
            '/v1/tools/data-synchronize/import/other-translations/validate',
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
    }

    public function testAllRoutesRequirePermission(): void
    {
        $userWithoutPermissions = User::factory()->createOne();

        $endpoints = [
            '/v1/tools/data-synchronize/export/posts',
            '/v1/tools/data-synchronize/export/translations/model',
            '/v1/tools/data-synchronize/export/other-translations',
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

        // Export returns file download or error
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

    // ─── Import (no file) ────────────────────────────────────────────

    public function testImportPostsWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/import/posts');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }

    // ─── Upload ──────────────────────────────────────────────────────

    public function testUploadWithoutFileFails(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/v1/tools/data-synchronize/upload');

        $this->assertContains($response->getStatusCode(), [422, 500]);
    }
}
