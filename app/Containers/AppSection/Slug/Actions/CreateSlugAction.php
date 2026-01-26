<?php

namespace App\Containers\AppSection\Slug\Actions;

use App\Containers\AppSection\Slug\Services\SlugService;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateSlugAction extends ParentAction
{
    public function __construct(
        private readonly SlugService $slugService,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    public function run(
        string $value,
        int|string|null $slugId = null,
        ?string $model = null,
        ?string $langCode = null
    ): string
    {
        $prefix = null;
        if ($model) {
            $prefix = $this->slugHelper->getPrefix($model);
        }

        return $this->slugService->create(
            $value,
            $slugId,
            $prefix,
            ! $this->slugHelper->turnOffAutomaticUrlTranslationIntoLatin(),
            $langCode,
        );
    }
}
