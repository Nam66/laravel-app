<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerformanceRecordIndexRequest;
use App\Services\PerformanceRecordQuery;

class PerformanceRecordController extends Controller
{
    public function __construct(private readonly PerformanceRecordQuery $query)
    {
    }

    public function index(PerformanceRecordIndexRequest $request)
    {
        $validated = $request->validated();
        $records = $this->query->apply($validated);

        return response()->json([
            'data' => $records->items(),
            'current_page' => $records->currentPage(),
            'last_page' => $records->lastPage(),
            'per_page' => $records->perPage(),
            'total' => $records->total(),
        ]);
    }
}