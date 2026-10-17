<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RedisRateLimitCommand extends Command
{
    protected $signature = 'redis:rate-limit {ip=127.0.0.1} {--limit=5 : Max requests in 60 seconds}';

    protected $description = 'Practice rate limiting with Redis cache';

    public function handle(): int
    {
        $ip = $this->argument('ip');
        $limit = (int) $this->option('limit');
        $key = "rate_limit:{$ip}";

        $current = (int) Cache::get($key, 0);

        if ($current >= $limit) {
            $this->warn("IP {$ip} has reached the limit ({$limit}). Request blocked.");

            return self::SUCCESS;
        }

        Cache::put($key, $current + 1, 60);

        $this->info("IP {$ip} allowed. Current count: ".($current + 1)." / {$limit}");

        return self::SUCCESS;
    }
}
