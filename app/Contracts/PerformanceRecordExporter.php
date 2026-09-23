<?php

namespace App\Contracts;

interface PerformanceRecordExporter
{
    public function export(iterable $records): string;
}
