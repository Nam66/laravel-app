<?php

namespace App\Services;

use App\Contracts\PerformanceRecordExporter;

class JsonPerformanceRecordExporter implements PerformanceRecordExporter
{
    public function export(iterable $records): string
    {
        return json_encode(array_map(static fn ($record) => [
            'id' => $record['id'] ?? ($record->id ?? null),
            'reference' => $record['reference'] ?? ($record->reference ?? null),
            'name' => $record['name'] ?? ($record->name ?? null),
            'email' => $record['email'] ?? ($record->email ?? null),
            'status' => $record['status'] ?? ($record->status ?? null),
            'amount' => $record['amount'] ?? ($record->amount ?? null),
            'created_at' => $record['created_at'] ?? ($record->created_at ?? null),
        ], iterator_to_array($records)), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
