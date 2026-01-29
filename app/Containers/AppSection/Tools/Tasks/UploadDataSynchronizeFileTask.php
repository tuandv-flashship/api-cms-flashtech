<?php

namespace App\Containers\AppSection\Tools\Tasks;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeStorage;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;

final class UploadDataSynchronizeFileTask extends ParentTask
{
    public function __construct(private readonly DataSynchronizeStorage $storage)
    {
    }

    /**
     * @return array{file_name: string, original_name: string, size: int, mime_type: string|null}
     */
    public function run(UploadedFile $file): array
    {
        return $this->storage->store($file);
    }
}
