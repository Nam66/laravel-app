<?php

namespace App\Services;

use App\Contracts\PerformanceRecordExporter;

class XlsxPerformanceRecordExporter implements PerformanceRecordExporter
{
    public function export(iterable $records): string
    {
        $rows = [
            ['id', 'reference', 'name', 'email', 'status', 'amount', 'created_at'],
        ];

        foreach ($records as $record) {
            $rows[] = [
                $record['id'] ?? ($record->id ?? ''),
                $record['reference'] ?? ($record->reference ?? ''),
                $record['name'] ?? ($record->name ?? ''),
                $record['email'] ?? ($record->email ?? ''),
                $record['status'] ?? ($record->status ?? ''),
                $record['amount'] ?? ($record->amount ?? ''),
                $record['created_at'] ?? ($record->created_at ?? ''),
            ];
        }

        $content = "";
        foreach ($rows as $row) {
            $content .= implode("\t", $row) . "\n";
        }

        return $content;
    }
}
