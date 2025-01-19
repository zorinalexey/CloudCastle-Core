<?php

namespace CloudCastle\Core\Traits;

trait Singleton
{
    private static $instance;
    
    private function __construct ()
    {
    
    }
    
    public static function getInstance (): self|null
    {
        return self::$instance;
    }
}