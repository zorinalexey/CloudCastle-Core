<?php

namespace CloudCastle\Core\Controllers;

use Throwable;
use function CloudCastle\Core\app;

final class ErrorController extends AbstractController
{
    public static function notFound (): array
    {
        return [
            'message' => '404 - Page not found!!!',
            'data' => [],
            'errors' => [],
            'success' => false,
            'code' => 404,
            'error' => true,
        ];
    }
    
    public static function exception (Throwable $e): array
    {
        return [
            'message' => $e->getMessage(),
            'data' => $e::class,
            'errors' => app()->config->app['debug']?$e->getTrace():[],
            'success' => false,
            'code' => 500,
            'error' => true,
        ];
    }
    
    public static function error (array $data): array
    {
        return [
            'message' => $data['message']??null,
            'data' => [],
            'errors' => [],
            'success' => false,
            'code' => $data['code']??404,
            'error' => true,
        ];
    }
}