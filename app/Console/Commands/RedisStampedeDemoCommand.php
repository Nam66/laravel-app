<?php

namespace App\Console\Commands;

use App\Services\StampedeSafeCache;
use Illuminate\Console\Command;

class RedisStampedeDemoCommand extends Command
{
    protected $signature = 'redis:stampede-demo {key=demo:expensive-report} {--refresh : Delete the cached value first}';

    protected $description = 'Demonstrate mutex locking and probabilistic early expiration';

    public function handle(StampedeSafeCache $stampedeSafeCache): int
    {
        $key = $this->argument('key');

        if ($this->option('refresh')) {
            cache()->store('redis')->forget($key);
            $this->info("Deleted {$key}");
        }

        $startedAt = microtime(true);
        $value = $stampedeSafeCache->remember(
            key: $key,
            freshForSeconds: 30,
            staleForSeconds: 30,
            earlyExpirationWindowSeconds: 10,
            loader: function (): array {
                $this->info('Cache miss or refresh owner acquired. Loading from the database...');
                sleep(2);

                return [
                    'generated_at' => now()->toDateTimeString(),
                    'report_total' => random_int(1000, 9999),
                ];
            },
        );

        $this->table(['field', 'value'], [
            ['generated_at', data_get($value, 'generated_at', 'null')],
            ['report_total', data_get($value, 'report_total', 'null')],
            ['duration_ms', round((microtime(true) - $startedAt) * 1000)],
        ]);

        return self::SUCCESS;
    }
}