<?php

namespace CloudCastle\Core\Request;

use stdClass;
use function CloudCastle\Core\{config, getRequestMethod, getRequestPath};

final class Request extends stdClass
{
    private static self|null $instance = null;
    public readonly string $method;
    public readonly string $path;
    
    private function __construct (array $data)
    {
        $this->method = getRequestMethod();
        $this->path = getRequestPath();
        
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
        
        $this->setParams();
    }
    
    private function setParams (): void
    {
        $files = $this->getFiles();
        $json = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $data = match ($this->method) {
            'POST', 'PUT', 'PATCH', 'DELETE' => [...$_GET, ...$_POST, ...$json, ...$files],
            default => [...$json, ...$_GET,],
        };
        
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
    
    public static function getInstance (): self
    {
        return self::$instance;
    }
    
    public static function init (array $data = []): self
    {
        self::$instance = new self($data);
        
        return self::$instance;
    }
    
    public function getProto (): string
    {
        if($proto = config('app')['protocol']??null){
            return $proto;
        }
        
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
    }
    
    public function getAllHeaders (): array
    {
        $headers = [];
        
        foreach (apache_request_headers() as $key => $value) {
            $headers[mb_strtolower($key)] = trim($value);
        }
        
        return $headers;
    }
    
    public function getBearerToken (): string|null
    {
        $token = $this->getAllHeaders()['authorization']??null;
        
        if ($token) {
            return trim(str_replace('Bearer', '', $token));
        }
        
        return null;
    }
    
    public function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = array_filter(array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
            $ip = reset($ip);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    private function getFiles (): array
    {
        if($_FILES) {
            $files = [];
            
            foreach ($_FILES as $key => $file) {
                if (is_array($_FILES[$key]['name'])) {
                    foreach ($_FILES[$key] as $name => $i) {
                        $file['name'] = $_FILES[$key]['name'][$i];
                        $file['tmp_name'] = $_FILES[$key]['tmp_name'][$i];
                        $file['type'] = $_FILES[$key]['type'][$i];
                        $file['size'] = $_FILES[$key]['size'][$i];
                        $file['error'] = $_FILES[$key]['error'][$i];
                        $files[$key][] = new UploadFile($file);
                    }
                }else{
                    $files[$key][] = new UploadFile($file);
                }
            }
            
            return $files;
        }
        
        return [];
    }
}