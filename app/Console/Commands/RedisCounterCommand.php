<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RedisCounterCommand extends Command
{
    protected $signature = 'redis:counter {key=page:view:home} {--increment=1 : Number to increment by}';

    protected $description = 'Practice Redis increment counters';

    public function handle(): int
    {
        $key = $this->argument('key');
        $increment = (int) $this->option('increment');

        Cache::set($key, Cache::get($key, 0));
        $newValue = Cache::increment($key, $increment);

        $this->info("Key: {$key}");
        $this->info("New value: {$newValue}");

        return self::SUCCESS;
    }
}
