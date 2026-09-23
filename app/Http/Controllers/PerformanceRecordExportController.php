<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerformanceRecordIndexRequest;
use App\Services\PerformanceRecordExportFactory;
use App\Services\PerformanceRecordQuery;
use InvalidArgumentException;

class PerformanceRecordExportController extends Controller
{
    public function __construct(
        private readonly PerformanceRecordQuery $query,
        private readonly PerformanceRecordExportFactory $factory,
    ) {
    }

    public function export(PerformanceRecordIndexRequest $request)
    {
        $validated = $request->validated();
        $records = $this->query->apply($validated)->getCollection();

        $format = strtolower((string) $request->query('format', 'csv'));
        $exporter = $this->factory->for($format);
        $content = $exporter->export($records);

        return match ($format) {
            'csv' => response($content, 200)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="performance-records.csv"'),
            'json' => response($content, 200)
                ->header('Content-Type', 'application/json; charset=UTF-8'),
            'excel', 'xlsx' => response($content, 200)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="performance-records.xlsx"'),
            default => throw new InvalidArgumentException('Unsupported export format: '.$format),
        };
    }
}
