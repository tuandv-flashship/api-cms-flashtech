<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Cache;

final class RecordPostViewTask extends ParentTask
{
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
        $post = Post::query()
            ->select(['id', 'views', 'updated_at'])
            ->findOrFail($postId);

        if (! (bool) config('blog.views.enabled', true)) {
            return [
                'recorded' => false,
                'views' => (int) $post->views,
            ];
        }

        $debounceSeconds = (int) config('blog.views.debounce_seconds', 900);
        $visitorKey = $this->resolveVisitorKey($userId, $sessionId, $ip, $userAgent);

        if ($debounceSeconds > 0) {
            $cacheKey = $this->buildCacheKey((int) $post->getKey(), $visitorKey);
            $recorded = Cache::add($cacheKey, true, $debounceSeconds);
        } else {
            $recorded = true;
        }

        if (! $recorded) {
            return [
                'recorded' => false,
                'views' => (int) $post->views,
            ];
        }

        Post::query()
            ->whereKey($post->getKey())
            ->increment('views', 1, ['updated_at' => $post->updated_at]);

        $views = Post::query()->whereKey($post->getKey())->value('views');

        return [
            'recorded' => true,
            'views' => (int) ($views ?? ($post->views + 1)),
        ];
    }

    private function resolveVisitorKey(
        ?string $userId,
        ?string $sessionId,
        ?string $ip,
        ?string $userAgent
    ): string {
        if ($userId !== null && $userId !== '') {
            return 'user:' . $userId;
        }

        if ($sessionId !== null && $sessionId !== '') {
            return 'session:' . $sessionId;
        }

        $key = 'ip:' . ($ip ?? 'unknown');

        if ($userAgent !== null && $userAgent !== '') {
            $key .= '|ua:' . sha1($userAgent);
        }

        return $key;
    }

    private function buildCacheKey(int $postId, string $visitorKey): string
    {
        $prefix = (string) config('blog.views.cache_prefix', 'blog:post:view');

        return $prefix . ':' . $postId . ':' . hash('sha256', $visitorKey);
    }
}
