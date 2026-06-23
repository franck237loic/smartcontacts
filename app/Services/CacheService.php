<?php

namespace App\Services;

/**
 * Cache Service
 * Handles caching with Redis or file-based fallback
 */
class CacheService
{
    private $redis;
    private $useRedis;
    private $cacheDir;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        $this->cacheDir = BASE_PATH . '/storage/cache';
        
        // Create cache directory if it doesn't exist
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        // Try to connect to Redis
        $this->useRedis = false;
        if (isset($config['redis']) && class_exists('Redis')) {
            try {
                $this->redis = new \Redis();
                $this->redis->connect(
                    $config['redis']['host'] ?? '127.0.0.1',
                    $config['redis']['port'] ?? 6379
                );
                if (isset($config['redis']['password'])) {
                    $this->redis->auth($config['redis']['password']);
                }
                if (isset($config['redis']['database'])) {
                    $this->redis->select($config['redis']['database']);
                }
                $this->useRedis = true;
            } catch (\Exception $e) {
                // Redis not available, fall back to file cache
                $this->useRedis = false;
            }
        }
    }

    /**
     * Get value from cache
     */
    public function get($key, $default = null)
    {
        if ($this->useRedis) {
            $value = $this->redis->get($key);
            return $value !== false ? json_decode($value, true) : $default;
        }

        // File-based cache
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['expires']) && $data['expires'] > time()) {
                return $data['value'];
            }
            // Cache expired, delete file
            unlink($file);
        }
        return $default;
    }

    /**
     * Set value in cache
     */
    public function set($key, $value, $ttl = 3600)
    {
        if ($this->useRedis) {
            return $this->redis->setex($key, $ttl, json_encode($value));
        }

        // File-based cache
        $file = $this->getCacheFile($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        return file_put_contents($file, json_encode($data)) !== false;
    }

    /**
     * Delete value from cache
     */
    public function delete($key)
    {
        if ($this->useRedis) {
            return $this->redis->del($key);
        }

        // File-based cache
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Clear all cache
     */
    public function clear()
    {
        if ($this->useRedis) {
            return $this->redis->flushDB();
        }

        // File-based cache
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    /**
     * Check if key exists in cache
     */
    public function has($key)
    {
        if ($this->useRedis) {
            return $this->redis->exists($key) > 0;
        }

        // File-based cache
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['expires']) && $data['expires'] > time()) {
                return true;
            }
            // Cache expired, delete file
            unlink($file);
        }
        return false;
    }

    /**
     * Get or set value (if not exists)
     */
    public function remember($key, $callback, $ttl = 3600)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Get cache file path
     */
    private function getCacheFile($key)
    {
        $safeKey = md5($key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }

    /**
     * Clean expired cache files
     */
    public function cleanExpired()
    {
        if ($this->useRedis) {
            // Redis handles expiration automatically
            return true;
        }

        $files = glob($this->cacheDir . '/*.cache');
        $now = time();
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['expires']) && $data['expires'] < $now) {
                unlink($file);
            }
        }
        return true;
    }
}
