<?php

namespace Tests\Unit;

use App\Services\PerformanceRecordExporter;
use PHPUnit\Framework\TestCase;

class PerformanceRecordExporterTest extends TestCase
{
    public function test_it_exports_records_to_csv(): void
    {
        $records = [
            [
                'id' => 1,
                'reference' => 'PERF-001',
                'name' => 'Alice, Nguyen',
                'email' => 'alice@example.com',
                'status' => 'active',
                'amount' => 150.00,
                'created_at' => '2026-09-20 10:00:00',
            ],
            [
                'id' => 2,
                'reference' => 'PERF-002',
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'status' => 'pending',
                'amount' => 200.10,
                'created_at' => '2026-09-21 11:00:00',
            ],
        ];

        $csv = (new PerformanceRecordExporter())->export($records);
        $lines = preg_split('/\R/', $csv);

        $this->assertNotEmpty($lines);
        $this->assertSame('id,reference,name,email,status,amount,created_at', $lines[0]);
        $this->assertStringContainsString('PERF-001', $csv);
        $this->assertStringContainsString('Alice', $csv);
        $this->assertStringContainsString('150', $csv);
        $this->assertStringContainsString('PERF-002', $csv);
    }
}
