<?php

namespace CloudCastle\Core\Auth;

use CloudCastle\Core\Traits\Singleton;
use stdClass;

final class Auth extends stdClass
{
    use Singleton;
    
    public static function login(array $data): self
    {
        self::init();
        $instance = self::getInstance();
        
        foreach ($data as $key => $value) {
            $instance->{$key} = $value;
        }
        
        return $instance;
    }
    
    private static function init (): void
    {
        if(!self::$instance){
            self::$instance = new self();
        }
    }
    
    public static function user(): self
    {
        return self::getInstance();
    }
    
    public function __get ($key): mixed
    {
        return $this->{$key}?? null;
    }
}