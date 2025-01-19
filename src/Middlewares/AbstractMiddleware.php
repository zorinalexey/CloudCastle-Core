<?php

namespace CloudCastle\Core\Middlewares;

abstract class AbstractMiddleware
{
    abstract public function check (): bool;
    
    abstract public function message (): string;
    
    abstract public function code (): int;
}