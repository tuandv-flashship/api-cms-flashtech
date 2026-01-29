<?php

namespace App\Containers\AppSection\Tools\Supports\Import;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeStorage;
use App\Containers\AppSection\Tools\Supports\SpreadsheetReader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

abstract class Importer
{
    public function __construct(
        private readonly SpreadsheetReader $reader,
        private readonly DataSynchronizeStorage $storage
    ) {
    }

    /**
     * @return array<int, ImportColumn>
     */
    abstract public function columns(): array;

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    abstract public function handle(array $rows): int;

    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function examples(): array
    {
        return [];
    }

    public function label(): string
    {
        return Str::of(static::class)
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->replace('importer', '')
            ->trim()
            ->title()
            ->toString();
    }

    public function validate(string $fileName, int $offset, int $limit, ?int $total = null): ValidationResult
    {
        $rows = $this->getRowsByOffset($fileName, $offset, $limit);
        $total = $total ?? $this->getTotalRows($fileName);

        $validator = Validator::make($rows, $this->getValidationRules(), [], $this->getAttributeNames($rows));
        $errors = $validator->fails() ? array_values(array_unique($validator->errors()->all())) : [];

        return new ValidationResult(
            offset: $offset,
            count: count($rows),
            total: $total,
            fileName: $fileName,
            errors: $errors
        );
    }

    public function import(string $fileName, int $offset, int $limit): ImportResult
    {
        $rows = $this->getRowsByOffset($fileName, $offset, $limit);
        $count = count($rows);

        [$validRows, $failures] = $this->filterInvalidRows($rows, $offset);

        $imported = $validRows !== [] ? $this->handle($validRows) : 0;

        if ($count === 0) {
            $this->storage->delete($fileName);
        }

        return new ImportResult(
            offset: $offset,
            count: $count,
            imported: $imported,
            failures: $failures
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function filterInvalidRows(array $rows, int $offset): array
    {
        $validator = Validator::make($rows, $this->getValidationRules(), [], $this->getAttributeNames($rows));
        if (! $validator->fails()) {
            return [$rows, []];
        }

        $errors = $validator->errors()->toArray();
        $invalidIndexes = [];
        $failures = [];

        foreach ($errors as $attribute => $messages) {
            [$rowIndex, $field] = $this->parseErrorKey($attribute);
            if ($rowIndex === null) {
                continue;
            }

            $invalidIndexes[$rowIndex] = true;

            $failures[] = [
                'row' => $offset + $rowIndex + 1,
                'attribute' => $field ?? $attribute,
                'errors' => $messages,
            ];
        }

        $validRows = array_values(array_filter($rows, static function (array $row, int $index) use ($invalidIndexes): bool {
            return ! isset($invalidIndexes[$index]);
        }, ARRAY_FILTER_USE_BOTH));

        return [$validRows, array_values($failures)];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRowsByOffset(string $fileName, int $offset, int $limit): array
    {
        $this->ensureSupportedFile($fileName);
        $path = $this->storage->path($fileName);
        if (! $this->storage->exists($fileName)) {
            throw new RuntimeException('File not found.');
        }

        $rows = $this->reader->read($path, $offset, $limit);

        return $this->transformRows($rows);
    }

    private function getTotalRows(string $fileName): int
    {
        $this->ensureSupportedFile($fileName);
        $path = $this->storage->path($fileName);
        if (! $this->storage->exists($fileName)) {
            throw new RuntimeException('File not found.');
        }

        return $this->reader->countRows($path);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    protected function transformRows(array $rows): array
    {
        $columns = $this->columns();

        return array_map(function (array $row) use ($columns): array {
            $formatted = [];

            foreach ($columns as $column) {
                $value = Arr::get($row, $column->getName());

                if ($column->isNullable() && $this->isEmpty($value)) {
                    $value = null;
                } elseif ($column->isBoolean() && is_string($value)) {
                    $value = $value === $column->getTrueValue() ? 1 : 0;
                }

                $formatted[$column->getName()] = $value;
            }

            return $this->mapRow($formatted);
        }, $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    protected function mapRow(array $row): array
    {
        return $row;
    }

    /**
     * @return array<int, string>
     */
    public function exampleHeaders(): array
    {
        return array_map(static fn (ImportColumn $column) => $column->getLabel(), $this->columns());
    }

    /**
     * @return iterable<int, array<int, mixed>>
     */
    public function exampleRows(): iterable
    {
        $columns = $this->columns();

        foreach ($this->examples() as $row) {
            $mapped = [];
            foreach ($columns as $column) {
                $mapped[] = $row[$column->getName()] ?? null;
            }
            yield $mapped;
        }
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->columns() as $column) {
            $rules['*.' . $column->getName()] = $column->getRules();
        }

        return $rules;
    }


    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<string, string>
     */
    private function getAttributeNames(array $rows): array
    {
        $attributes = [];
        $columnNames = array_map(static fn (ImportColumn $column) => $column->getName(), $this->columns());

        foreach ($rows as $index => $row) {
            foreach ($columnNames as $name) {
                $attributes[$index . '.' . $name] = $index . '.' . $name;
            }
        }

        return $attributes;
    }

    /**
     * @return array{0: int|null, 1: string|null}
     */
    private function parseErrorKey(string $attribute): array
    {
        $parts = explode('.', $attribute, 2);
        if (count($parts) !== 2) {
            return [null, null];
        }

        $rowIndex = is_numeric($parts[0]) ? (int) $parts[0] : null;

        return [$rowIndex, $parts[1]];
    }

    private function ensureSupportedFile(string $fileName): void
    {
        $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
        $extensions = (array) config('data-synchronize.extensions', ['csv', 'xlsx']);

        if ($extension === '' || ! in_array($extension, $extensions, true)) {
            throw new RuntimeException('Unsupported file format.');
        }
    }
}
