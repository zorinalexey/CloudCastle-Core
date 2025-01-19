<?php

namespace CloudCastle\Core\Controllers;

use CloudCastle\Core\Services\AbstractService;
use function CloudCastle\Core\app;

abstract class AbstractController
{
    protected AbstractService|null $service = null;
    
    public function __construct ()
    {
        $this->service = $this->getService();
    }
    
    private function getService (): AbstractService|null
    {
        $service = str_replace('Controller', 'Service', get_called_class());
        
        if (class_exists($service)) {
            return new $service();
        }
        
        return null;
    }
    
    public static function getCacheKeys (array|string|int|null $data = []): array
    {
        return [
            'collection' => app()->env->entity . ":collection:" . md5(json_encode([$data, __METHOD__])),
            'id' => app()->env->entity . ":id:" . md5(json_encode([$data, __METHOD__])),
        ];
    }
}