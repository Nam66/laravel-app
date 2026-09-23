<?php

namespace Tests\Unit;

use App\Contracts\PerformanceRecordExporter;
use App\Services\CsvPerformanceRecordExporter;
use App\Services\JsonPerformanceRecordExporter;
use App\Services\PerformanceRecordExportFactory;
use App\Services\XlsxPerformanceRecordExporter;
use PHPUnit\Framework\TestCase;

class PerformanceRecordExportFactoryTest extends TestCase
{
    public function test_it_returns_the_correct_exporter_for_each_format(): void
    {
        $factory = new PerformanceRecordExportFactory();

        $this->assertInstanceOf(CsvPerformanceRecordExporter::class, $factory->for('csv'));
        $this->assertInstanceOf(JsonPerformanceRecordExporter::class, $factory->for('json'));
        $this->assertInstanceOf(XlsxPerformanceRecordExporter::class, $factory->for('excel'));
        $this->assertInstanceOf(PerformanceRecordExporter::class, $factory->for('csv'));
    }
}
