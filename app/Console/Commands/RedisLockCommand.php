<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RedisLockCommand extends Command
{
    protected $signature = 'redis:lock-demo {name=payment-job}';

    protected $description = 'Practice distributed lock with Redis';

    public function handle(): int
    {
        $lock = Cache::lock('redis:lock:'.$this->argument('name'), 10);

        if ($lock->get()) {
            $this->info('Lock acquired successfully.');
            $this->info('Processing critical section...');
            $lock->release();
            $this->info('Lock released.');

            return self::SUCCESS;
        }

        $this->warn('Could not acquire lock. Another process is running.');

        return self::SUCCESS;
    }
}
