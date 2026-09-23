<?php

namespace Tests\Feature;

use App\Models\PerformanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceRecordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_performance_records_and_returns_paginated_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/performance-records?'.http_build_query([
            'search' => 'Alice',
            'status' => 'active',
            'date_from' => '2026-09-20',
            'min_amount' => 100,
            'max_amount' => 200,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'reference', 'name', 'email', 'status', 'amount', 'created_at']],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.reference', 'PERF-001')
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('data.0.amount', '150.00');
    }
}
