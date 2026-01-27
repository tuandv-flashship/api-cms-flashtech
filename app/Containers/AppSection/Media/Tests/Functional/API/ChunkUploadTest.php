<?php

namespace App\Containers\AppSection\Media\Tests\Functional\API;

use App\Containers\AppSection\Media\Supports\MediaSettingsStore;
use App\Containers\AppSection\Media\Tests\Functional\ApiTestCase;
use App\Containers\AppSection\Media\UI\API\Controllers\UploadMediaFileController;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UploadMediaFileController::class)]
final class ChunkUploadTest extends ApiTestCase
{
    public function testChunkUploadFlow(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        config()->set('media.chunk.enabled', true);
        config()->set('media.chunk.storage.disk', 'local');
        config()->set('media.chunk.storage.chunks', 'chunks');
        config()->set('media.settings_defaults.media_chunk_enabled', true);
        config()->set('media.settings_defaults.media_max_file_size', 2048);

        app(MediaSettingsStore::class)->clear();

        $this->actingAs(User::factory()->superAdmin()->createOne());

        $uuid = (string) Str::uuid();
        $chunkSize = 1024;
        $totalSize = 2048;

        $firstChunk = UploadedFile::fake()->create('chunk.txt', 1, 'text/plain');
        $response = $this->postJson(action(UploadMediaFileController::class), [
            'file' => $firstChunk,
            'dzuuid' => $uuid,
            'dzchunkindex' => 0,
            'dztotalchunkcount' => 2,
            'dztotalfilesize' => $totalSize,
            'dzchunksize' => $chunkSize,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.chunked', true);

        $secondChunk = UploadedFile::fake()->create('chunk.txt', 1, 'text/plain');
        $response = $this->postJson(action(UploadMediaFileController::class), [
            'file' => $secondChunk,
            'dzuuid' => $uuid,
            'dzchunkindex' => 1,
            'dztotalchunkcount' => 2,
            'dztotalfilesize' => $totalSize,
            'dzchunksize' => $chunkSize,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => ['id', 'src', 'url'],
        ]);
    }
}
