<?php

namespace CloudCastle\Core\Cache;

use CloudCastle\Core\App\App;
use CloudCastle\Core\Controllers\AbstractController;
use CloudCastle\Core\Traits\Singleton;
use Predis\Client;
use function CloudCastle\Core\app;
use function CloudCastle\Core\config;

final class Cache
{
    use Singleton;
    
    private readonly Client $redis;
    private int $timeout = 0;
    
    public static function init ()
    {
        self::$instance = new self();
        $config = config('cache')['redis'];
        $params = [
            'host' => $config['host'],
            'port' => $config['port'],
            'password' => $config['password'],
        ];
        $client = new Client($params);
        $client->connect();
        $client->select($config['database']);
        self::$instance->redis = $client;
        self::$instance->timeout = $config['timeout'];
        App::set('cache', self::getInstance());
        App::set(self::class, self::getInstance());
    }
    
    public function set (string $key, mixed $data, int|null $ttl = null): mixed
    {
        if($data === null) {
            return null;
        }
        
        if (is_callable($data)) {
            $data = $data();
        }
        
        if (!$ttl) {
            $ttl = $this->timeout;
        }
        
        $this->redis->set($key, json_encode($data ?: []), 'EX', time() + $ttl);
        
        return $data;
    }
    
    public function delete (string $key): bool
    {
        return (bool) $this->redis->del($key);
    }
    
    public function flush (string $id): void
    {
        $entity = app()->env->entity;
        $data = ['id' => $id];
        $cacheKey = "{$entity}:id:" . md5(json_encode($data));
        
        $this->redis->del($cacheKey);
        $this->redis->del("{$entity}:collection:*");
        
        foreach (AbstractController::getCacheKeys() as $key) {
            $this->redis->del($key);
        }
    }
    
    public function clear (): bool
    {
        return (bool) $this->redis->flushAll();
    }
    
    public function remember (string $key, mixed $data, int|null $ttl = null): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }
        
        return $this->set($key, $data, $ttl);
    }
    
    public function get (string $key): mixed
    {
        return json_decode($this->redis->get($key), true) ?? [];
    }
    
    public function rememberForever (string $key, mixed $data): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }
        
        return $this->redis->set($key, $data);
    }
}