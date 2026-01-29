<?php

namespace App\Containers\AppSection\Tools\Supports;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\WriterInterface;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

final class SpreadsheetWriter
{
    /**
     * @param array<int, string> $headers
     * @param iterable<int, array<int, mixed>> $rows
     */
    public function write(string $format, array $headers, iterable $rows, string $path): void
    {
        $writer = $this->makeWriter($format);

        File::ensureDirectoryExists(dirname($path));
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues($headers));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
    }

    private function makeWriter(string $format): WriterInterface
    {
        return match (strtolower($format)) {
            'csv' => new CsvWriter(),
            'xlsx' => new XlsxWriter(),
            default => throw new InvalidArgumentException('Unsupported export format.'),
        };
    }
}
