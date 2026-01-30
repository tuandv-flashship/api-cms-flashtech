<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Supports\CustomFieldOptions;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Ship\Parents\Actions\Action as ParentAction;
use InvalidArgumentException;

final class ListCustomFieldBoxesAction extends ParentAction
{
    public function __construct(
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * @param array<string, mixed> $rules
     * @return array<int, array<string, mixed>>
     */
    public function run(string $model, ?int $referenceId, array $rules = []): array
    {
        $referenceType = CustomFieldOptions::resolveModelClass($model);
        if (! $referenceType) {
            throw new InvalidArgumentException('Unsupported model.');
        }

        return $this->customFieldService->exportCustomFieldsData(
            $referenceType,
            $referenceId,
            $rules
        );
    }
}
