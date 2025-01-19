<?php

namespace CloudCastle\Core\Middlewares;

use CloudCastle\Core\App\Auth\ApiTokenService;
use CloudCastle\Core\Auth\Auth;
use CloudCastle\Core\Request\Request;
use CloudCastle\Core\Services\EmployeeService;
use function CloudCastle\Core\trans;

final class ApiAuthMiddleware extends AbstractMiddleware
{
    
    public function check (): bool
    {
        $request = Request::getInstance();
        $token = $request->getBearerToken()??'';
        $user_agent = $request->getAllHeaders()['user-agent']??'';
        $token = (new ApiTokenService())->view($token, $user_agent);
        
        if($token && strtotime($token['expires_at']) >= time()) {
            $employee = (new EmployeeService())->view($token['employee_id'], 'default');
            Auth::login($employee);
            
            return (bool)Auth::user();
        }
        
        return false;
    }
    
    public function message (): string
    {
        return trans('auth', 'Permission denied. User not authorized');
    }
    
    public function code (): int
    {
        return 401;
    }
}