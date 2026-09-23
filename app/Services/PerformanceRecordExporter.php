<?php

namespace App\Services;

use App\Contracts\PerformanceRecordExporter;

class CsvPerformanceRecordExporter implements PerformanceRecordExporter
{
    /**
     * @param iterable<int, array<string, mixed>|object> $records
     */
    public function export(iterable $records): string
    {
        $rows = [
            ['id', 'reference', 'name', 'email', 'status', 'amount', 'created_at'],
        ];

        foreach ($records as $record) {
            $rows[] = $this->normalizeRow($record);
        }

        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return rtrim((string) $csv, "\n");
    }

    /**
     * @param array<string, mixed>|object $record
     * @return array<int, mixed>
     */
    protected function normalizeRow(mixed $record): array
    {
        if (is_array($record)) {
            return [
                $record['id'] ?? '',
                $record['reference'] ?? '',
                $record['name'] ?? '',
                $record['email'] ?? '',
                $record['status'] ?? '',
                $record['amount'] ?? '',
                $record['created_at'] ?? '',
            ];
        }

        return [
            $record->id ?? '',
            $record->reference ?? '',
            $record->name ?? '',
            $record->email ?? '',
            $record->status ?? '',
            $record->amount ?? '',
            $record->created_at ?? '',
        ];
    }
}
