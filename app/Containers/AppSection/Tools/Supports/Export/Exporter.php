<?php

namespace App\Containers\AppSection\Tools\Supports\Export;

use Carbon\Carbon;
use Illuminate\Support\Str;

abstract class Exporter
{
    private ?array $acceptedColumns = null;
    private string $format = 'csv';

    /**
     * @return array<int, ExportColumn>
     */
    abstract public function columns(): array;

    /**
     * @return iterable<int, array<string, mixed>>
     */
    abstract protected function getRows(): iterable;

    public function label(): string
    {
        return Str::of(static::class)
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->replace('exporter', '')
            ->trim()
            ->title()
            ->toString();
    }

    public function format(string $format): self
    {
        $this->format = strtolower($format);

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function acceptedColumns(?array $columns): self
    {
        $this->acceptedColumns = $columns ? array_values($columns) : null;

        return $this;
    }

    /**
     * @return array<int, ExportColumn>
     */
    public function getAcceptedColumns(): array
    {
        $columns = $this->columns();
        if ($this->acceptedColumns === null) {
            return $columns;
        }

        return array_values(array_filter($columns, function (ExportColumn $column): bool {
            return in_array($column->getName(), $this->acceptedColumns, true);
        }));
    }

    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return array_map(static fn (ExportColumn $column) => $column->getLabel(), $this->getAcceptedColumns());
    }

    /**
     * @return iterable<int, array<int, mixed>>
     */
    public function rows(): iterable
    {
        $columns = $this->getAcceptedColumns();

        foreach ($this->getRows() as $row) {
            $mapped = [];
            foreach ($columns as $column) {
                $mapped[] = $row[$column->getName()] ?? null;
            }

            yield $mapped;
        }
    }

    public function fileName(): string
    {
        return sprintf(
            '%s-%s.%s',
            Str::slug($this->label()),
            Carbon::now()->format('Y-m-d-H-i-s'),
            $this->format
        );
    }
}
