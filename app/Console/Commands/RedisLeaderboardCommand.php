<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisLeaderboardCommand extends Command
{
    protected $signature = 'redis:leaderboard {key=game:leaderboard} {--limit=10 : Number of top players to show} {--seed : Insert demo data first}';

    protected $description = 'Demo Redis leaderboard using sorted sets';

    public function handle(): int
    {
        $key = $this->argument('key');
        $limit = (int) $this->option('limit');

        if ($this->option('seed')) {
            $this->seedLeaderboard($key);
        }

        $scores = Redis::zrevrange($key, 0, $limit - 1, ['withscores' => true]);

        if (empty($scores)) {
            $this->warn("No leaderboard data found for {$key}. Run with --seed to create sample data.");

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($scores as $member => $score) {
            $rows[] = [count($rows) + 1, $member, $score];
        }

        $this->info("Leaderboard: {$key}");
        $this->table(['rank', 'member', 'score'], $rows);

        return self::SUCCESS;
    }

    protected function seedLeaderboard(string $key): void
    {
        $data = [
            'alice' => 900,
            'bob' => 840,
            'charlie' => 760,
            'diana' => 710,
            'edward' => 680,
            'frank' => 620,
        ];

        foreach ($data as $member => $score) {
            Redis::zadd($key, [$member => $score]);
        }

        $this->info("Seeded sample leaderboard data for {$key}");
    }
}
