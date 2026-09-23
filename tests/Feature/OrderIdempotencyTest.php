<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeating_a_request_with_the_same_key_returns_the_same_order(): void
    {
        $user = User::factory()->create();
        $payload = [
            'item_name' => 'Test subscription',
            'quantity' => 1,
            'amount' => 19.99,
            'currency' => 'usd',
        ];

        $firstResponse = $this->actingAs($user, 'api')
            ->withHeader('Idempotency-Key', 'order-test-001')
            ->postJson('/api/orders', $payload);
        $secondResponse = $this->actingAs($user, 'api')
            ->withHeader('Idempotency-Key', 'order-test-001')
            ->postJson('/api/orders', $payload);

        $firstResponse->assertCreated()
            ->assertHeader('Idempotency-Replayed', 'false')
            ->assertJsonPath('idempotency_replayed', false);
        $secondResponse->assertOk()
            ->assertHeader('Idempotency-Replayed', 'true')
            ->assertJsonPath('idempotency_replayed', true)
            ->assertJsonPath('data.id', $firstResponse->json('data.id'));
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_reusing_a_key_with_a_different_payload_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->withHeader('Idempotency-Key', 'order-test-002')
            ->postJson('/api/orders', [
                'item_name' => 'Test subscription',
                'quantity' => 1,
                'amount' => 19.99,
                'currency' => 'USD',
            ])
            ->assertCreated();

        $this->actingAs($user, 'api')
            ->withHeader('Idempotency-Key', 'order-test-002')
            ->postJson('/api/orders', [
                'item_name' => 'Different subscription',
                'quantity' => 1,
                'amount' => 19.99,
                'currency' => 'USD',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This Idempotency-Key was already used with a different request.');
    }

    public function test_the_idempotency_key_is_required(): void
    {
        $this->actingAs(User::factory()->create(), 'api')
            ->postJson('/api/orders', [])
            ->assertBadRequest();
    }
}