<?php

namespace App\Containers\AppSection\LanguageAdvanced\Actions;

use App\Containers\AppSection\LanguageAdvanced\Models\SlugTranslation;
use App\Containers\AppSection\LanguageAdvanced\Tasks\UpsertSlugTranslationTask;
use App\Containers\AppSection\Slug\Models\Slug;
use App\Containers\AppSection\Slug\Services\SlugService;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\QueryException;

final class UpdateSlugTranslationAction extends ParentAction
{
    public function __construct(
        private readonly UpsertSlugTranslationTask $upsertSlugTranslationTask,
        private readonly SlugHelper $slugHelper,
        private readonly SlugService $slugService,
    ) {
    }

    public function run(int $slugId, string $langCode, string $key, ?string $model = null): SlugTranslation
    {
        $slug = Slug::query()->findOrFail($slugId);
        $prefix = $this->resolvePrefix($slug, $model);
        $attempts = 0;
        $maxAttempts = 5;

        while (true) {
            $slugKey = $this->slugService->create(
                $key,
                $slug->getKey(),
                $prefix,
                ! $this->slugHelper->turnOffAutomaticUrlTranslationIntoLatin(),
                $langCode,
            );

            try {
                return $this->upsertSlugTranslationTask->run($slug->getKey(), $langCode, $slugKey, $prefix);
            } catch (QueryException $exception) {
                if (! $this->isDuplicateKeyException($exception) || $attempts >= $maxAttempts) {
                    throw $exception;
                }

                $attempts++;
            }
        }
    }

    private function resolvePrefix(Slug $slug, ?string $model): string
    {
        $prefix = $slug->prefix ?? '';
        $modelClass = $model ?: $slug->reference_type;

        if ($modelClass && class_exists($modelClass)) {
            $prefix = $this->slugHelper->getPrefix($modelClass, $prefix);
        }

        return $prefix ?? '';
    }

    private function isDuplicateKeyException(QueryException $exception): bool
    {
        $errorInfo = $exception->errorInfo;

        if (is_array($errorInfo) && isset($errorInfo[1]) && (int) $errorInfo[1] === 1062) {
            return true;
        }

        return $exception->getCode() === '23000';
    }
}
