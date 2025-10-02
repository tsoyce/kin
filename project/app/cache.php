<?php

declare(strict_types=1);

namespace Project;

final class Cache
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?: base_path('storage/cache');
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    private function path(string $key): string
    {
        return $this->directory . '/' . sha1($key) . '.cache.php';
    }

    public function get(string $key, callable $callback, int $ttl = 300)
    {
        $file = $this->path($key);
        if (is_file($file)) {
            $payload = include $file;
            if (is_array($payload) && isset($payload['expires']) && $payload['expires'] >= time()) {
                return $payload['value'];
            }
        }

        $value = $callback();
        $data = var_export([
            'expires' => time() + $ttl,
            'value' => $value,
        ], true);
        file_put_contents($file, "<?php\nreturn {$data};\n");
        return $value;
    }

    public function forget(string $key): void
    {
        $file = $this->path($key);
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function clear(): void
    {
        foreach (glob($this->directory . '/*.cache.php') as $file) {
            unlink($file);
        }
    }
}
