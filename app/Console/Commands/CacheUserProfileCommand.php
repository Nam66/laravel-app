<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheUserProfileCommand extends Command
{
    protected $signature = 'redis:user-profile {userId=1} {--refresh : Force refresh cache}';

    protected $description = 'Practice caching user profile in Redis';

    public function handle(): int
    {
        $userId = (int) $this->argument('userId');
        $key = "user:profile:{$userId}";

        if ($this->option('refresh')) {
            Cache::forget($key);
            $this->info("Cache cleared for {$key}");
        }

        $user = Cache::remember($key, 300, function () use ($userId) {
            $this->info("Cache miss for user {$userId}. Querying database...");

            return User::find($userId) ?? ['id' => $userId, 'name' => 'Unknown', 'email' => 'unknown@example.com'];
        });

        $this->table(['field', 'value'], [
            ['id', data_get($user, 'id', 'n/a')],
            ['name', data_get($user, 'name', 'n/a')],
            ['email', data_get($user, 'email', 'n/a')],
        ]);

        return self::SUCCESS;
    }
}
