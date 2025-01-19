<?php

namespace CloudCastle\Core\Resources;

use CloudCastle\Core\Request\Request;
use function CloudCastle\Core\{app, trans};

final class CollectionLinkResource extends AbstractResource
{
    public function toArray (): array
    {
        return [
            'list' => $this->getLink('list'),
            'create' => $this->getLink('create'),
            'group_delete_hard' => $this->getLink('group_delete_hard'),
            'group_delete_soft' => $this->getLink('group_delete_soft'),
            'group_restore' => $this->getLink('group_restore'),
        ];
    }
    
    private function getLink (string $action): array
    {
        $request = Request::getInstance();
        $uri = trim($this->prefix, '/') . 'CollectionLinkResource.php/' . $this->entity;
        $permission = str_replace('/', '.', $uri . '/' . $action);
        [$method, $suffix] = $this->getParams($action);
        $url = $request->getProto() . '://' . app()->env->app_host . '/' . $uri . $suffix;
        
        return [
            'title' => trans(app()->env->entity."_routes", $permission, [':id' => $this->id]),
            'method' => $method,
            'permission_key' => mb_strtolower($method . '.' . $permission),
            'name_key' => $permission,
            'url' => $url,
        ];
    }
    
    private function getParams (string $action): array
    {
        return match ($action) {
            'list' => ['GET', ''],
            'create' => ['POST', ''],
            'group_delete_hard' => ['DELETE', '/group/hard'],
            'group_delete_soft' => ['DELETE', '/group/soft'],
            'group_restore' => ['PUT', '/group'],
            default => ['GET', ''],
        };
    }
}