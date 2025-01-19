<?php

namespace CloudCastle\Core\Middlewares;

use function CloudCastle\Core\trans;

final class RolePermissionMiddleware extends AbstractMiddleware
{
    
    public function check (): bool
    {
        return true;
    }
    
    public function message (): string
    {
        return trans('permissions', 'not allowed');
    }
    
    public function code (): int
    {
        return 403;
    }
}