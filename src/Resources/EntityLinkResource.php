<?php

namespace CloudCastle\Core\Resources;

use CloudCastle\Core\Request\Request;
use function CloudCastle\Core\{app, trans};

final class EntityLinkResource extends AbstractResource
{
    public function toArray (): array
    {
        return [
            'view' => $this->getLink('view'),
            'restore' => $this->getLink('restore'),
            'update' => $this->getLink('update'),
            'delete_soft' => $this->getLink('delete_soft'),
            'delete_hard' => $this->getLink('delete_hard'),
        ];
    }
    
    private function getLink (string $action): array
    {
        
        $request = Request::getInstance();
        $uri = trim($this->prefix, '/') . 'EntityLinkResource.php/' . $this->entity;
        $permission = str_replace('/', '.', $uri . '/' . $action);
        $method = $this->getMethod($action);
        $url = $request->getProto() . '://' . app()->env->app_host . '/' . $uri;
        
        if ($this->id) {
            $url .= '/' . $this->id;
        }
        
        return [
            'title' => trans("{$this->entity}_routes", $permission, [':id' => $this->id]),
            'method' => $method,
            'permission_key' => mb_strtolower($method . '.' . $permission),
            'name_key' => $permission,
            'url' => $url,
        ];
    }
    
    private function getMethod (string $action)
    {
        return match ($action) {
            'restore' => 'PUT',
            'view' => 'GET',
            'update' => 'PATCH',
            'delete_soft' => 'DELETE',
            'delete_hard' => 'DELETE',
            default => 'GET',
        };
    }
}