<?php

namespace App\Containers\AppSection\Tools\Supports;

use InvalidArgumentException;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

final class SpreadsheetReader
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function read(string $path, int $offset, int $limit): array
    {
        $rows = [];
        $header = null;
        $dataIndex = 0;

        $reader = $this->makeReader($path);
        $reader->open($path);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();

                if ($header === null) {
                    $header = $this->normalizeHeaders($cells);
                    continue;
                }

                if ($this->isEmptyRow($cells)) {
                    continue;
                }

                $dataIndex++;
                if ($dataIndex <= $offset) {
                    continue;
                }

                $rows[] = $this->mapRow($header, $cells);

                if ($limit > 0 && count($rows) >= $limit) {
                    break 2;
                }
            }

            break;
        }

        $reader->close();

        return $rows;
    }

    public function countRows(string $path): int
    {
        $count = 0;
        $header = null;

        $reader = $this->makeReader($path);
        $reader->open($path);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();

                if ($header === null) {
                    $header = $this->normalizeHeaders($cells);
                    continue;
                }

                if ($this->isEmptyRow($cells)) {
                    continue;
                }

                $count++;
            }

            break;
        }

        $reader->close();

        return $count;
    }

    /**
     * @param array<int, mixed> $header
     * @return array<int, string>
     */
    private function normalizeHeaders(array $header): array
    {
        $normalized = [];
        foreach ($header as $cell) {
            $value = is_string($cell) ? $cell : (string) $cell;
            $normalized[] = $this->normalizeHeaderValue($value);
        }

        return $normalized;
    }

    private function normalizeHeaderValue(string $value): string
    {
        $value = ltrim($value, "\xEF\xBB\xBF");
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[^\\pL\\pN]+/u', '_', $value);
        $value = strtolower((string) $value);
        $value = preg_replace('/_+/', '_', $value);

        return trim((string) $value, '_');
    }

    /**
     * @param array<int, string> $header
     * @param array<int, mixed> $cells
     * @return array<string, mixed>
     */
    private function mapRow(array $header, array $cells): array
    {
        $mapped = [];
        foreach ($header as $index => $key) {
            if ($key === '') {
                continue;
            }

            $mapped[$key] = $cells[$index] ?? null;
        }

        return $mapped;
    }

    /**
     * @param array<int, mixed> $cells
     */
    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell === null) {
                continue;
            }

            if (is_string($cell) && trim($cell) === '') {
                continue;
            }

            return false;
        }

        return true;
    }

    private function makeReader(string $path): ReaderInterface
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => new CsvReader(),
            'xlsx' => new XlsxReader(),
            default => throw new InvalidArgumentException('Unsupported import format.'),
        };
    }
}
