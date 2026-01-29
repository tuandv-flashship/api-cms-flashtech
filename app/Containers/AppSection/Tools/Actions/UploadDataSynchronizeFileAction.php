<?php

namespace App\Containers\AppSection\Tools\Actions;

use App\Containers\AppSection\Tools\Tasks\UploadDataSynchronizeFileTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Http\UploadedFile;

final class UploadDataSynchronizeFileAction extends ParentAction
{
    public function __construct(private readonly UploadDataSynchronizeFileTask $task)
    {
    }

    /**
     * @return array{file_name: string, original_name: string, size: int, mime_type: string|null}
     */
    public function run(UploadedFile $file): array
    {
        return $this->task->run($file);
    }
}
