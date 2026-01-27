<?php

namespace App\Containers\AppSection\Media\UI\API\Controllers;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ShowMediaFileController extends ApiController
{
    public function __invoke(string $hash, string $id): Response|BinaryFileResponse|RedirectResponse
    {
        if (sha1($id) !== $hash) {
            abort(404);
        }

        $fileId = hexdec($id);
        $file = MediaFile::query()->findOrFail($fileId);

        $service = app(MediaService::class);
        $disk = $file->visibility === 'private' ? $service->getPrivateDisk() : $service->getDisk();

        if ($file->visibility === 'public') {
            return redirect()->to($service->url($file->url));
        }

        $accessMode = $service->resolveAccessModeForFile($file);
        if ($accessMode === 'signed') {
            if (! request()->hasValidSignature()) {
                abort(403);
            }
        } elseif (! auth()->check()) {
            abort(403);
        }

        $content = Storage::disk($disk)->get($file->url);

        return response($content, 200, [
            'Content-Type' => $file->mime_type,
        ]);
    }
}
