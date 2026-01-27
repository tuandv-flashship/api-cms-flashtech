<?php

namespace App\Containers\AppSection\Media\Actions;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Http\UploadedFile;

final class UploadMediaFileAction extends ParentAction
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    public function run(
        UploadedFile $file,
        int $folderId,
        int $userId,
        ?string $visibility = null,
        ?string $accessMode = null
    ): MediaFile
    {
        return $this->mediaService->storeUploadedFile($file, $folderId, $userId, $visibility, $accessMode);
    }
}
