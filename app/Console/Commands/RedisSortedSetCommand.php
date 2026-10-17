<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisSortedSetCommand extends Command
{
    protected $signature = 'redis:sorted-set {key=leaderboard:game} {member=player1} {score=100} {--action=add : add|list|rank|increment} {--limit=10}';

    protected $description = 'Practice Redis sorted sets and score ranking';

    public function handle(): int
    {
        $action = $this->option('action');
        $key = $this->argument('key');
        $member = $this->argument('member');
        $score = (float) $this->argument('score');
        $limit = (int) $this->option('limit');

        return match ($action) {
            'add' => $this->add($key, $member, $score),
            'list' => $this->list($key, $limit),
            'rank' => $this->rank($key, $member),
            'increment' => $this->increment($key, $member, $score),
            default => $this->showUsage(),
        };
    }

    protected function add(string $key, string $member, float $score): int
    {
        Redis::zadd($key, [$member => $score]);

        $this->info("Added {$member} with score {$score} to {$key}");

        return self::SUCCESS;
    }

    protected function list(string $key, int $limit): int
    {
        $items = Redis::zrevrange($key, 0, $limit - 1, ['withscores' => true]);

        if (empty($items)) {
            $this->warn("Sorted set {$key} is empty.");

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($items as $member => $score) {
            $rows[] = [$member, $score];
        }

        $this->table(['member', 'score'], $rows);

        return self::SUCCESS;
    }

    protected function rank(string $key, string $member): int
    {
        $rank = Redis::zrevrank($key, $member);

        if ($rank === null) {
            $this->warn("Member {$member} does not exist in {$key}");

            return self::SUCCESS;
        }

        $this->info("{$member} is ranked #" . ($rank + 1) . " in {$key}");

        return self::SUCCESS;
    }

    protected function increment(string $key, string $member, float $score): int
    {
        $newScore = Redis::zincrby($key, $score, $member);

        $this->info("{$member} score updated to {$newScore} in {$key}");

        return self::SUCCESS;
    }

    protected function showUsage(): int
    {
        $this->warn('Unsupported action. Use: add, list, rank, increment');

        return self::FAILURE;
    }
}
