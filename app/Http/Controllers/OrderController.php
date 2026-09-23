<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $validated['currency'] = strtoupper($validated['currency']);
        $idempotencyKey = $request->header('Idempotency-Key');
        $requestHash = hash('sha256', json_encode($validated, JSON_THROW_ON_ERROR));
        $userId = $request->user()->id;

        try {
            $order = DB::transaction(function () use ($idempotencyKey, $requestHash, $validated, $userId) {
                $existingOrder = Order::where('user_id', $userId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existingOrder) {
                    $this->ensureSameRequest($existingOrder, $requestHash);

                    return $existingOrder;
                }

                return Order::create([
                    ...$validated,
                    'user_id' => $userId,
                    'idempotency_key' => $idempotencyKey,
                    'request_hash' => $requestHash,
                ]);
            });
        } catch (QueryException $exception) {
            $order = Order::where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if (! $order) {
                throw $exception;
            }

            $this->ensureSameRequest($order, $requestHash);
        }

        $replayed = $order->wasRecentlyCreated === false;

        return response()->json([
            'data' => $order,
            'idempotency_replayed' => $replayed,
        ], $replayed ? 200 : 201)->header('Idempotency-Replayed', $replayed ? 'true' : 'false');
    }

    private function ensureSameRequest(Order $order, string $requestHash): void
    {
        if (! hash_equals($order->request_hash, $requestHash)) {
            abort(422, 'This Idempotency-Key was already used with a different request.');
        }
    }
}