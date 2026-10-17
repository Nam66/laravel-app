<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class RedisPracticeCommand extends Command
{
    protected $signature = 'redis:practice {task=all}';

    protected $description = 'Practice Redis cache tasks for learning and demo purposes';

    public function handle(): int
    {
        $task = $this->argument('task');

        match ($task) {
            'all' => $this->runAll(),
            'set' => $this->demoSet(),
            'get' => $this->demoGet(),
            'remember' => $this->demoRemember(),
            'ttl' => $this->demoTtl(),
            'increment' => $this->demoIncrement(),
            'delete' => $this->demoDelete(),
            'hash' => $this->demoHash(),
            'list' => $this->demoList(),
            'lock' => $this->demoLock(),
            default => $this->error("Unknown task: {$task}. Available: all, set, get, remember, ttl, increment, delete, hash, list, lock"),
        };

        return self::SUCCESS;
    }

    protected function runAll(): void
    {
        $this->info('=== Redis Practice Demo ===');

        $this->demoSet();
        $this->demoGet();
        $this->demoRemember();
        $this->demoTtl();
        $this->demoIncrement();
        $this->demoHash();
        $this->demoList();
        $this->demoLock();

        $this->info('All Redis practice tasks completed.');
    }

    protected function demoSet(): void
    {
        $this->info('Task 1: SET cache key');
        Cache::put('redis_practice:name', 'Laravel Redis Demo', 60);
        $this->info('Stored redis_practice:name = Laravel Redis Demo');
    }

    protected function demoGet(): void
    {
        $this->info('Task 2: GET cache key');
        $value = Cache::get('redis_practice:name');
        $this->info('Value: '.($value ?? 'null'));
    }

    protected function demoRemember(): void
    {
        $this->info('Task 3: remember() - cache miss then cache value');

        $value = Cache::remember('redis_practice:count', 120, function () {
            return rand(100, 999);
        });

        $this->info('Stored value: '.$value);
    }

    protected function demoTtl(): void
    {
        $this->info('Task 4: TTL');
        Cache::put('redis_practice:ttl', 'expires soon', 30);
        $ttl = Cache::ttl('redis_practice:ttl');
        $this->info('TTL for redis_practice:ttl = '.$ttl.' seconds');
    }

    protected function demoIncrement(): void
    {
        $this->info('Task 5: INCR');
        Cache::set('redis_practice:counter', 0);
        Cache::increment('redis_practice:counter');
        Cache::increment('redis_practice:counter', 2);
        $this->info('Counter value: '.Cache::get('redis_practice:counter'));
    }

    protected function demoDelete(): void
    {
        $this->info('Task 6: DELETE cache key');
        Cache::put('redis_practice:delete_me', 'will be removed', 60);
        Cache::forget('redis_practice:delete_me');
        $this->info('redis_practice:delete_me deleted: '.(Cache::get('redis_practice:delete_me') ?? 'null'));
    }

    protected function demoHash(): void
    {
        $this->info('Task 7: Redis hash');
        Redis::hset('redis_practice:user:1', 'name', 'Alice');
        Redis::hset('redis_practice:user:1', 'email', 'alice@example.com');
        $user = Redis::hgetall('redis_practice:user:1');
        $this->table(['field', 'value'], array_map(fn ($key, $val) => [$key, $val], array_keys($user), array_values($user)));
    }

    protected function demoList(): void
    {
        $this->info('Task 8: Redis list');
        Redis::del('redis_practice:tasks');
        Redis::rpush('redis_practice:tasks', 'task-1');
        Redis::rpush('redis_practice:tasks', 'task-2');
        Redis::rpush('redis_practice:tasks', 'task-3');

        $tasks = Redis::lrange('redis_practice:tasks', 0, -1);
        $this->table(['index', 'task'], array_map(fn ($task, $index) => [$index, $task], $tasks, array_keys($tasks)));
    }

    protected function demoLock(): void
    {
        $this->info('Task 9: Redis lock');

        $lock = Cache::lock('redis_practice:job:lock', 10);

        if ($lock->get()) {
            $this->info('Lock acquired successfully.');
            $lock->release();
        } else {
            $this->warn('Could not acquire lock.');
        }
    }
}
