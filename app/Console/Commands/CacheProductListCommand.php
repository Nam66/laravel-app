<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheProductListCommand extends Command
{
    protected $signature = 'redis:products {--refresh : Clear cached product list}';

    protected $description = 'Practice caching product list in Redis';

    public function handle(): int
    {
        $key = 'catalog:products';

        if ($this->option('refresh')) {
            Cache::forget($key);
            $this->info('Product cache cleared.');
        }

        $products = Cache::remember($key, 60, function () {
            $this->info('Cache miss. Generating product list...');

            return [
                ['id' => 1, 'name' => 'Laptop', 'price' => 1500],
                ['id' => 2, 'name' => 'Mouse', 'price' => 50],
                ['id' => 3, 'name' => 'Keyboard', 'price' => 120],
            ];
        });

        $this->table(['id', 'name', 'price'], array_map(fn ($p) => [$p['id'], $p['name'], $p['price']], $products));

        return self::SUCCESS;
    }
}
