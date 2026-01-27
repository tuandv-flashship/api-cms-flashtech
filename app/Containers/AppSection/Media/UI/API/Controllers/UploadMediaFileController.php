<?php

namespace App\Containers\AppSection\Media\UI\API\Controllers;

use App\Containers\AppSection\Media\Actions\UploadMediaFileAction;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Containers\AppSection\Media\Supports\MediaSettingsStore;
use App\Containers\AppSection\Media\UI\API\Requests\UploadMediaFileRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UploadMediaFileController extends ApiController
{
    public function __invoke(UploadMediaFileRequest $request, UploadMediaFileAction $action): JsonResponse
    {
        $settings = app(MediaSettingsStore::class);
        $totalChunks = (int) $request->input('dztotalchunkcount', 1);
        $file = $request->file('file');
        $maxSize = $settings->getInt('media_max_file_size', (int) config('media.chunk.max_file_size', 0));

        if ($totalChunks > 1) {
            if (! $settings->getBool('media_chunk_enabled', (bool) config('media.chunk.enabled', false))) {
                return response()->json(['message' => 'Chunk upload is disabled.'], 422);
            }

            if (! $request->filled('dzuuid')) {
                return response()->json(['message' => 'Missing chunk uuid.'], 422);
            }

            if (! $request->has('dzchunkindex')) {
                return response()->json(['message' => 'Missing chunk index.'], 422);
            }

            $totalSize = (int) $request->input('dztotalfilesize', 0);
            $chunkSize = (int) $request->input('dzchunksize', 0);

            if ($maxSize > 0 && $totalSize <= 0) {
                return response()->json(['message' => 'Missing total file size.'], 422);
            }

            if ($maxSize > 0 && $totalSize > $maxSize) {
                return response()->json(['message' => 'File size exceeds the allowed limit.'], 422);
            }

            if ($totalSize > 0 && $chunkSize > 0) {
                $expectedChunks = (int) ceil($totalSize / $chunkSize);
                if ($expectedChunks !== $totalChunks) {
                    return response()->json(['message' => 'Chunk count does not match file size.'], 422);
                }
            }

            return $this->handleChunkUpload($request, $action, $totalChunks);
        }

        if ($file instanceof UploadedFile && $maxSize > 0 && $file->getSize() > $maxSize) {
            return response()->json(['message' => 'File size exceeds the allowed limit.'], 422);
        }

        $file = $action->run(
            $file,
            (int) $request->input('folder_id', 0),
            (int) $request->user()->getKey(),
            $request->input('visibility'),
            $request->input('access_mode'),
        );

        $service = app(MediaService::class);
        $signedUrl = $service->getSignedUrl($file);
        $accessMode = $service->resolveAccessModeForFile($file);

        return response()->json([
            'data' => [
                'id' => $file->getHashedKey(),
                'src' => $file->visibility === 'public' ? $service->url($file->url) : $signedUrl,
                'url' => $file->url,
                'access_mode' => $accessMode,
                'signed_url' => $signedUrl,
            ],
        ]);
    }

    private function handleChunkUpload(
        UploadMediaFileRequest $request,
        UploadMediaFileAction $action,
        int $totalChunks
    ): JsonResponse {
        $chunkIndex = (int) $request->input('dzchunkindex', 0);
        $uuid = (string) $request->input('dzuuid', (string) Str::uuid());
        $file = $request->file('file');

        if ($chunkIndex >= $totalChunks) {
            return response()->json(['message' => 'Invalid chunk index.'], 422);
        }

        if (! $file instanceof UploadedFile) {
            return response()->json(['message' => 'Missing chunk file.'], 422);
        }

        $disk = (string) config('media.chunk.storage.disk', 'local');
        $baseDir = trim((string) config('media.chunk.storage.chunks', 'chunks'), '/');
        $chunkDir = $baseDir . '/' . $uuid;

        Storage::disk($disk)->makeDirectory($chunkDir);
        Storage::disk($disk)->putFileAs($chunkDir, $file, $chunkIndex . '.part');

        $done = (int) ceil((($chunkIndex + 1) / max(1, $totalChunks)) * 100);

        if (($chunkIndex + 1) < $totalChunks) {
            return response()->json([
                'data' => [
                    'chunked' => true,
                    'done' => $done,
                    'chunk_index' => $chunkIndex,
                    'chunk_total' => $totalChunks,
                ],
            ]);
        }

        if (! $this->hasAllChunks($disk, $chunkDir, $totalChunks)) {
            return response()->json([
                'data' => [
                    'chunked' => true,
                    'done' => $done,
                    'chunk_index' => $chunkIndex,
                    'chunk_total' => $totalChunks,
                ],
            ], 202);
        }

        $originalName = (string) $request->input('filename', $file->getClientOriginalName());
        $assembledPath = $this->assembleChunks($disk, $chunkDir, $totalChunks, $originalName);

        $uploadedFile = new UploadedFile(
            $assembledPath,
            $originalName,
            $file->getClientMimeType() ?: null,
            null,
            true
        );

        try {
            $mediaFile = $action->run(
                $uploadedFile,
                (int) $request->input('folder_id', 0),
                (int) $request->user()->getKey(),
                $request->input('visibility'),
                $request->input('access_mode'),
            );
        } finally {
            if (is_file($assembledPath)) {
                @unlink($assembledPath);
            }

            Storage::disk($disk)->deleteDirectory($chunkDir);
        }

        $service = app(MediaService::class);
        $signedUrl = $service->getSignedUrl($mediaFile);
        $accessMode = $service->resolveAccessModeForFile($mediaFile);

        return response()->json([
            'data' => [
                'id' => $mediaFile->getHashedKey(),
                'src' => $mediaFile->visibility === 'public' ? $service->url($mediaFile->url) : $signedUrl,
                'url' => $mediaFile->url,
                'access_mode' => $accessMode,
                'signed_url' => $signedUrl,
            ],
        ]);
    }

    private function hasAllChunks(string $disk, string $chunkDir, int $totalChunks): bool
    {
        for ($index = 0; $index < $totalChunks; $index++) {
            if (! Storage::disk($disk)->exists($chunkDir . '/' . $index . '.part')) {
                return false;
            }
        }

        return true;
    }

    private function assembleChunks(string $disk, string $chunkDir, int $totalChunks, string $originalName): string
    {
        $tempDir = storage_path('app/tmp/media');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $targetPath = $tempDir . '/' . Str::uuid() . '-' . basename($originalName);
        $target = fopen($targetPath, 'wb');

        for ($index = 0; $index < $totalChunks; $index++) {
            $chunkPath = $chunkDir . '/' . $index . '.part';
            $stream = Storage::disk($disk)->readStream($chunkPath);

            if (! is_resource($stream)) {
                if (is_resource($target)) {
                    fclose($target);
                }
                throw new \RuntimeException('Missing chunk data.');
            }

            stream_copy_to_stream($stream, $target);
            fclose($stream);
        }

        fclose($target);

        return $targetPath;
    }
}
