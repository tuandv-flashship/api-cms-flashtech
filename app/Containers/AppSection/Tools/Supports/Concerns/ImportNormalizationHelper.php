<?php

namespace App\Containers\AppSection\Tools\Supports\Concerns;

use App\Containers\AppSection\Blog\Enums\ContentStatus;

/**
 * Shared normalization helpers for importers that deal with status and string values.
 */
trait ImportNormalizationHelper
{
    protected function normalizeString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : (is_string($value) ? $value : null);
    }

    protected function normalizeStatus(mixed $value): ContentStatus
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';

        foreach (ContentStatus::cases() as $status) {
            if ($status->value === $value) {
                return $status;
            }
        }

        return ContentStatus::PUBLISHED;
    }
}
