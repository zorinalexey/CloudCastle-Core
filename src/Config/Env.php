<?php

namespace CloudCastle\Core\Config;

use CloudCastle\Core\App\App;
use CloudCastle\Core\Traits\Singleton;
use stdClass;

final class Env extends stdClass
{
    use Singleton;
    
    public static function init (string $path): self
    {
        self::$instance = new self();
        
        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) if (str_contains($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                putenv(sprintf('%s=%s', $key, $value));
                self::$instance->{$key} = $value;
            }
        }
        
        App::set('env', self::getInstance());
        App::set(self::class, self::getInstance());
        
        return self::getInstance();
    }
    
    public function __get (string $env): mixed
    {
        return $this->get($env);
    }
    
    public function get (string $env, mixed $default = null)
    {
        $property = mb_strtoupper($env);
        
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
        
        return $default;
    }
}