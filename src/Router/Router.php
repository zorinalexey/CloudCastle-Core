<?php

namespace CloudCastle\Core\Router;

use CloudCastle\Core\Controllers\ErrorController;
use CloudCastle\Core\Middlewares\AbstractMiddleware;
use CloudCastle\Core\Request\Request;

final class Router
{
    public static function run (string $controller, array $middlewares, array $action): array|object
    {
        $method = $action['method'];
        
        if (class_exists($controller) && method_exists($controller, $method)) {
            
            foreach ($middlewares as $middleware) {
                if(class_exists($middleware) && ($middleware = new $middleware()) && ($middleware instanceof AbstractMiddleware) && !$middleware->check()){
                    $data = [
                        'code' => $middleware->code(),
                        'message' => $middleware->message(),
                    ];
                    
                    return ErrorController::error($data);
                }
            }
            
            $controller = new $controller();
            
            return $controller->$method(...self::setParams($action['params']));
        }
        
        return ErrorController::notFound();
    }
    
    private static function setParams (array $params): array
    {
        if (!$params) {
            return [];
        }
        
        $request = Request::getInstance();
        $data = [];
        
        foreach ($params as $key => $value) {
            if (class_exists($value)) {
                $data[$key] = new $value();
            } else {
                $data[$key] = self::getRequestParam($key, $request);
            }
        }
        
        return $data;
    }
    
    private static function getRequestParam (string $key, Request $request): mixed
    {
        foreach ((array) $request as $name => $value) {
            if ((string)$name === $key) {
                return $value;
            }
        }
        
        return null;
    }
}