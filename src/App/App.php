<?php

namespace CloudCastle\Core\App;

use CloudCastle\Core\Cache\Cache;
use CloudCastle\Core\Config\Config;
use CloudCastle\Core\Config\Env;
use CloudCastle\Core\DB\DB;
use CloudCastle\Core\Traits\Singleton;

/**
 * @property Cache $cache
 * @property DB $db
 * @property Env $env
 * @property Config $config
 */
final class App
{
    use Singleton;
    
    private array $app = [];
    
    public static function set (string $key, mixed $value): void
    {
        if (!($obj = self::getInstance())) {
            $obj = self::init();
        }
        
        $obj->app[$key] = $value;
    }
    
    private static function init (): self
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function __get (string $key): mixed
    {
        return $this->get($key);
    }
    
    public function get (string $key, mixed $default = null): mixed
    {
        return $this->app[$key] ?? $default;
    }
}