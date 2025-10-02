<?php

declare(strict_types=1);

namespace Project\Services;

use Project\Cache;
use function Project\base_path;

final class RateLimiter
{
    private Cache $cache;
    private string $key;
    private int $maxAttempts;
    private int $ttl;

    public function __construct(string $key, int $maxAttempts = 5, int $ttl = 300)
    {
        $this->cache = new Cache();
        $this->key = 'ratelimit:' . $key;
        $this->maxAttempts = $maxAttempts;
        $this->ttl = $ttl;
    }

    public function check(): bool
    {
        $attempts = $this->cache->get($this->key, fn () => ['attempts' => 0, 'expires' => time() + $this->ttl], $this->ttl);
        return ($attempts['attempts'] ?? 0) < $this->maxAttempts;
    }

    public function hit(): void
    {
        $this->cache->get($this->key, function () {
            return ['attempts' => 1, 'expires' => time() + $this->ttl];
        }, $this->ttl);
        $file = base_path('storage/cache/' . sha1($this->key) . '.cache.php');
        if (is_file($file)) {
            $payload = include $file;
            if (is_array($payload)) {
                $payload['value']['attempts'] = ($payload['value']['attempts'] ?? 0) + 1;
                $payload['value']['expires'] = time() + $this->ttl;
                $data = var_export($payload, true);
                file_put_contents($file, "<?php\nreturn {$data};\n");
            }
        }
    }

    public function reset(): void
    {
        $this->cache->forget($this->key);
    }
}
