<?php

namespace CloudCastle\Core {
    
    use CloudCastle\Core\App\App;
    use CloudCastle\Core\Config\Config;
    use CloudCastle\Core\Config\Env;
    use CloudCastle\Core\Controllers\ErrorController;
    use CloudCastle\Core\Exceptions\StorageException;
    use CloudCastle\Core\Lang\Lang;
    use CloudCastle\Core\Request\Request;
    use CloudCastle\Core\Storage\StorageInterface;
    
    function getRequestPath (): string
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrl);
        
        return '/' . trim(mb_strtolower($parsedUrl['path']), '/');
    }
    
    function getRequestMethod (): string
    {
        return mb_strtoupper($_SERVER['REQUEST_METHOD']);
    }
    
    function getRoute (array $routes): array
    {
        $requestMethod = getRequestMethod();
        
        foreach ($routes as $pattern => $route) {
            if (preg_match($pattern, getRequestPath(), $matches) && isset($route['actions'][$requestMethod]) && $method = $route['actions'][$requestMethod]) {
                $requestData = [
                    'files' => $_FILES,
                ];
                $route['action'] = $method;
                
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $requestData[$key] = urldecode($value);
                    }
                }
                
                Request::init($requestData);
                unset($route['actions'], $requestData, $method);
                
                return $route;
            }
        }
        
        return [
            'controller' => ErrorController::class,
            'action' => [
                'action' => 'notFound',
                'params' => [],
            ],
            'middlewares' => [],
        ];
    }
    
    function env (string|null $name = null, mixed $default = null): mixed
    {
        $instance = Env::getInstance();
        
        if (!$name) {
            return $instance;
        }
        
        if ($name = $instance->get($name, $default)) {
            return $name;
        }
        
        return $default;
    }
    
    function config (string|null $name = null, mixed $default = null): mixed
    {
        $instance = Config::getInstance();
        
        if (!$name) {
            return $instance;
        }
        
        if ($name = $instance->get($name, $default)) {
            return $name;
        }
        
        return $default;
    }
    
    function scan_dir (string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        
        $data = [];
        
        $files = scandir($path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            if (is_dir($path . '/' . $file)) {
                $data = [...scan_dir($path . '/' . $file), ...$data];
            }
            
            if (is_file($path . '/' . $file)) {
                $data[] = $path . '/' . $file;
            }
        }
        
        return $data;
    }
    
    function response (array|object $data): string
    {
        $responseMessage = $data['message'] ?? 'Ok';
        $result = [
            'data' => [],
            'errors' => [],
            'code' => 200,
            'error' => false,
            'success' => true,
            'message' => trans('response', $responseMessage),
            ...$data
        ];
        
        if ($result['success'] && !$result['data']) {
            http_response_code(204);
        } else {
            http_response_code($result ['code']);
        }
        
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    
    function app (): App
    {
        return App::getInstance();
    }
    
    function trans (string $name, string $key, array $params = []): string
    {
        $langObj = Lang::getInstance();
        
        if (($arr = $langObj->{$name}) && is_array($arr) && isset($arr[$key])) {
            return str_replace(array_keys($params), array_values($params), $arr[$key]);
        }
        
        return "{$name}.{$key}";
    }
    
    function getDiskUsage(string $diskName, array $diskConfig): StorageInterface
    {
        $disk = config('filesystem')[$diskName]??null;
        
        if(class_exists($disk['class']) && ($obj = new ($disk['class'])(...$diskConfig)) && $obj instanceof StorageInterface){
            return $obj;
        }
        
        throw new StorageException("Disk [$diskName] not configured");
    }
}