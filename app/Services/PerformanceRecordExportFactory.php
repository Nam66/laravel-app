<?php

namespace App\Services;

use App\Contracts\PerformanceRecordExporter;
use InvalidArgumentException;

class PerformanceRecordExportFactory
{
    /**
     * @return PerformanceRecordExporter
     */
    public function for(string $format): PerformanceRecordExporter
    {
        return match (strtolower($format)) {
            'csv' => new CsvPerformanceRecordExporter(),
            'json' => new JsonPerformanceRecordExporter(),
            'excel', 'xlsx' => new XlsxPerformanceRecordExporter(),
            default => throw new InvalidArgumentException('Unsupported export format: '.$format),
        };
    }
}
