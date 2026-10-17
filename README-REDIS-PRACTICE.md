# Redis Practice Tasks

## 1. Cache user profile
Command:
```bash
php artisan redis:user-profile 1
php artisan redis:user-profile 1 --refresh
```

Goal:
- learn `Cache::remember()`
- cache user profile for 5 minutes
- clear cache when needed

## 2. Cache product list
Command:
```bash
php artisan redis:products
php artisan redis:products --refresh
```

Goal:
- learn caching list data
- clear stale data after update

## 3. Counter / view count
Command:
```bash
php artisan redis:counter page:view:home --increment=1
php artisan redis:counter page:view:home --increment=10
```

Goal:
- learn `Cache::increment()`
- count visits, likes, page views

## 4. Rate limit
Command:
```bash
php artisan redis:rate-limit 127.0.0.1 --limit=5
```

Goal:
- block too many requests from one IP
- learn TTL + key count logic

## 5. Distributed lock
Command:
```bash
php artisan redis:lock-demo payment-job
```

Goal:
- learn `Cache::lock()`
- prevent duplicate execution in distributed env

## Example code snippets

### Cache value
```php
Cache::put('user:1', $user, 300);
```

### Read cache
```php
$user = Cache::get('user:1');
```

### Remember with fallback
```php
$user = Cache::remember('user:1', 300, function () {
    return User::find(1);
});
```

### Increment counter
```php
Cache::increment('page:view:home');
```

### Lock critical section
```php
$lock = Cache::lock('payment:123', 10);
if ($lock->get()) {
    // critical section
    $lock->release();
}
```

## Cache Stampede / Thundering Herd

Cache stampede happens when a popular key expires and many requests query the
database at the same time to rebuild the same value. This can overload the
database even though the application only needs one copy of the result.

Run the demo with Redis as the cache store:
```bash
php artisan redis:stampede-demo report:daily --refresh
php artisan redis:stampede-demo report:daily
```

The example in `app/Services/StampedeSafeCache.php` uses three techniques:

- a Redis mutex allows only one request to run the expensive loader;
- requests with stale data receive the old value while the owner refreshes it;
- probabilistic early expiration makes requests near expiry gradually more
    likely to refresh, spreading refresh work over time.

For local practice, set `CACHE_STORE=redis` in `.env` when the rest of the
application should also use Redis. The demo explicitly selects the `redis`
store and therefore does not depend on the default cache store.
