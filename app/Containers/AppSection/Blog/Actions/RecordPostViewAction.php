<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Tasks\RecordPostViewTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class RecordPostViewAction extends ParentAction
{
    public function __construct(private readonly RecordPostViewTask $recordPostViewTask)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function run(
        int $postId,
        ?string $userId,
        ?string $sessionId,
        ?string $ip,
        ?string $userAgent
    ): array {
        return $this->recordPostViewTask->run($postId, $userId, $sessionId, $ip, $userAgent);
    }
}
