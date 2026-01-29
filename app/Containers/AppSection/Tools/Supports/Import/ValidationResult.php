<?php

namespace App\Containers\AppSection\Tools\Supports\Import;

final class ValidationResult
{
    /**
     * @param array<int, string> $errors
     */
    public function __construct(
        public readonly int $offset,
        public readonly int $count,
        public readonly int $total,
        public readonly string $fileName,
        public readonly array $errors,
    ) {
    }
}
