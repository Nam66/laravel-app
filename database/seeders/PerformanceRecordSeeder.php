<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $total = (int) env('PERFORMANCE_RECORDS_COUNT', 1000000);
        $batchSize = 2000;
        $statuses = ['active', 'pending', 'inactive', 'archived'];
        $now = now();

        for ($offset = 0; $offset < $total; $offset += $batchSize) {
            $rows = [];
            $limit = min($batchSize, $total - $offset);

            for ($index = 0; $index < $limit; $index++) {
                $number = $offset + $index + 1;

                $rows[] = [
                    'reference' => 'PERF-'.str_pad((string) $number, 10, '0', STR_PAD_LEFT),
                    'name' => 'Test Customer '.$number,
                    'email' => 'customer'.$number.'@example.test',
                    'status' => $statuses[$number % count($statuses)],
                    'amount' => number_format(($number % 100000) / 100 + 10, 2, '.', ''),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('performance_records')->insert($rows);

            $processed = $offset + $limit;
            $this->command?->getOutput()->writeln("Inserted {$processed}/{$total} records");
        }
    }
}