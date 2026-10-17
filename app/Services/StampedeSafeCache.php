<?php

namespace App\Services;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class StampedeSafeCache
{
    /**
     * Return cached data, refresh it once when needed, and serve stale data
     * while another request owns the refresh lock.
     */
    public function remember(
        string $key,
        int $freshForSeconds,
        int $staleForSeconds,
        Closure $loader,
        int $earlyExpirationWindowSeconds = 0,
    ): mixed {
        $cache = Cache::store('redis');
        $entry = $cache->get($key);
        $now = now()->getTimestamp();

        if ($entry !== null && $this->isFresh($entry, $now, $earlyExpirationWindowSeconds)) {
            return $entry['value'];
        }

        $lock = $cache->lock("{$key}:refresh-lock", 15);

        if ($lock->get()) {
            try {
                // Another request may have refreshed the value just before we got the lock.
                $entry = $cache->get($key);
                if ($entry !== null && $this->isFresh($entry, now()->getTimestamp(), 0)) {
                    return $entry['value'];
                }

                $value = $loader();
                $freshUntil = now()->addSeconds($freshForSeconds);
                $cache->put($key, [
                    'value' => $value,
                    'fresh_until' => $freshUntil->getTimestamp(),
                    'stale_until' => $freshUntil->addSeconds($staleForSeconds)->getTimestamp(),
                ], $freshForSeconds + $staleForSeconds);

                return $value;
            } finally {
                $lock->release();
            }
        }

        if ($entry !== null && $entry['stale_until'] >= $now) {
            return $entry['value'];
        }

        // A cold-cache request waits briefly for the single refresh owner.
        try {
            return $lock->block(5, function () use ($cache, $key, $loader, $freshForSeconds, $staleForSeconds) {
                $entry = $cache->get($key);
                if ($entry !== null && $entry['fresh_until'] > now()->getTimestamp()) {
                    return $entry['value'];
                }

                $value = $loader();
                $freshUntil = now()->addSeconds($freshForSeconds);
                $cache->put($key, [
                    'value' => $value,
                    'fresh_until' => $freshUntil->getTimestamp(),
                    'stale_until' => $freshUntil->addSeconds($staleForSeconds)->getTimestamp(),
                ], $freshForSeconds + $staleForSeconds);

                return $value;
            });
        } catch (LockTimeoutException) {
            return $entry['value'] ?? null;
        }
    }

    private function isFresh(array $entry, int $now, int $earlyExpirationWindowSeconds): bool
    {
        $freshUntil = (int) ($entry['fresh_until'] ?? 0);

        if ($freshUntil <= $now) {
            return false;
        }

        if ($earlyExpirationWindowSeconds <= 0) {
            return true;
        }

        $secondsLeft = $freshUntil - $now;
        if ($secondsLeft > $earlyExpirationWindowSeconds) {
            return true;
        }

        // Gradually increase refresh probability as expiry approaches.
        $refreshProbability = 1 - ($secondsLeft / $earlyExpirationWindowSeconds);

        return (random_int(1, 1000000) / 1000000) > $refreshProbability;
    }
}