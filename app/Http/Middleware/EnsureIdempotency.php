<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if (! is_string($idempotencyKey) || trim($idempotencyKey) === '') {
            return response()->json(['message' => 'The Idempotency-Key header is required.'], 400);
        }

        if (strlen($idempotencyKey) > 255) {
            return response()->json(['message' => 'The Idempotency-Key header may not be greater than 255 characters.'], 422);
        }

        $requestHash = $this->requestHash($request);
        $redisKey = sprintf(
            'idempotency:%s:%s',
            $request->user()->getAuthIdentifier(),
            hash('sha256', $idempotencyKey)
        );
        $redis = Redis::connection('cache');
        $reservation = json_encode([
            'status' => 'processing',
            'request_hash' => $requestHash,
        ], JSON_THROW_ON_ERROR);
        $wasReserved = $redis->set($redisKey, $reservation, 'EX', $this->ttl(), 'NX');

        if ($wasReserved !== true && $wasReserved !== 'OK') {
            $stored = json_decode((string) $redis->get($redisKey), true);

            if (($stored['request_hash'] ?? null) !== $requestHash) {
                return response()->json([
                    'message' => 'This Idempotency-Key was already used with a different request.',
                ], 422);
            }

            if (($stored['status'] ?? null) === 'processing') {
                return response()->json([
                    'message' => 'This Idempotency-Key is currently being processed.',
                ], 409);
            }

            return response($stored['body'] ?? '{}', $stored['status_code'] ?? 200)
                ->header('Content-Type', 'application/json')
                ->header('Idempotency-Replayed', 'true');
        }

        try {
            $response = $next($request);

            if ($response->getStatusCode() >= 500) {
                $redis->del($redisKey);

                return $response;
            }

            $completed = json_encode([
                'status' => 'completed',
                'request_hash' => $requestHash,
                'status_code' => $response->getStatusCode(),
                'body' => $response->getContent(),
            ], JSON_THROW_ON_ERROR);
            $redis->set($redisKey, $completed, 'EX', $this->ttl());

            return $response->header('Idempotency-Replayed', 'false');
        } catch (\Throwable $exception) {
            $redis->del($redisKey);

            throw $exception;
        }
    }

    private function requestHash(Request $request): string
    {
        $payload = $request->all();
        $this->sortRecursively($payload);

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function sortRecursively(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursively($item);
            }
        }

        if (! array_is_list($value)) {
            ksort($value);
        }
    }

    private function ttl(): int
    {
        return max(1, (int) config('idempotency.ttl', 86400));
    }
}