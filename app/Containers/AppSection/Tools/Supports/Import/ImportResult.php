<?php

namespace App\Containers\AppSection\Tools\Supports\Import;

final class ImportResult
{
    /**
     * @param array<int, array<string, mixed>> $failures
     */
    public function __construct(
        public readonly int $offset,
        public readonly int $count,
        public readonly int $imported,
        public readonly array $failures,
    ) {
    }
}
