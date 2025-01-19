<?php

namespace CloudCastle\Core\Lang;

use CloudCastle\Core\App\App;
use CloudCastle\Core\Traits\Singleton;
use stdClass;
use function CloudCastle\Core\scan_dir;

final class Lang extends stdClass
{
    use Singleton;
    
    public static function init (string $path): self
    {
        self::$instance = new self();
        
        foreach (scan_dir($path) as $file) {
            if (is_file($file)) {
                $key = str_replace([$path . '/', '.php', DIRECTORY_SEPARATOR], ['', '', '.'], $file);
                self::$instance->{$key} = require $file;
            }
        }
        
        App::set('lang', self::getInstance());
        App::set(self::class, self::getInstance());
        
        return self::$instance;
    }
    
    public function __get (string $key): mixed
    {
        return $this->get($key);
    }
    
    public function get (string $key): mixed
    {
        return $this->{$key} ?? $key;
    }
}