<?php

namespace App\Containers\AppSection\CustomField\Traits;

use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;

/**
 * Trait for models that support custom fields (Post, Page, Category, etc.).
 *
 * Provides:
 * - Cascade delete of custom_fields + translations when model is deleted
 * - Helper to retrieve custom field boxes for the model
 */
trait HasCustomFields
{
    protected static function bootHasCustomFields(): void
    {
        static::deleted(function (self $model): void {
            CustomField::query()
                ->where('use_for', static::class)
                ->where('use_for_id', $model->getKey())
                ->each(static fn (CustomField $field) => $field->delete());
        });
    }

    /**
     * Get all custom field boxes for this model, filtered by rules and locale.
     *
     * @param  array<string, mixed>  $rules  Extra rule context for conditional logic
     * @return array<int, array<string, mixed>>
     */
    public function getCustomFieldBoxes(?string $locale = null, array $rules = []): array
    {
        return app(CustomFieldService::class)->exportCustomFieldsData(
            static::class,
            $this->getKey(),
            $rules,
            $locale
        );
    }
}
