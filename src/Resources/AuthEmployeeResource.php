<?php

namespace CloudCastle\Core\Resources;

use function CloudCastle\Core\app;

final class AuthEmployeeResource extends AbstractResource
{
    public function toArray (): array
    {
        $prefix = trim(app()->env->api_prefix, '/').'/crm';
        
        return [
            'employee' => [
                'id' => $this->e_id,
                'int_id' => $this->e_int_id,
                'call_number' => $this->e_virtual_number,
                'date_of_birth' => $this->e_date_of_birth,
                'full_name' => $this->full_name,
                'is_blocked' => (bool)$this->e_is_blocked,
                'last_name' => $this->e_last_name,
                'email' => $this->e_email,
                'login' => $this->e_login,
                'middle_name' => $this->e_middle_name,
                'name' => $this->e_name,
                'phone' => $this->e_phone,
                'virtual_number' => $this->e_virtual_number,
                'city' => [
                    'id' => $this->ct_id,
                    'int_id' => $this->ct_int_id,
                    'name' => $this->ct_name,
                    'links' => EntityLinkResource::make(['id' => $this->ct_id, 'entity' => 'city', 'prefix' => $prefix])
                ],
                'region' => [
                    'id' => $this->rg_id,
                    'int_id' => $this->rg_int_id,
                    'name' => $this->rg_name,
                    'links' => EntityLinkResource::make(['id' => $this->rg_id, 'entity' => 'region', 'prefix' => $prefix])
                ],
                'country' => [
                    'id' => $this->co_id,
                    'int_id' => $this->co_int_id,
                    'name' => $this->co_name,
                    'links' => EntityLinkResource::make(['id' => $this->co_id, 'entity' => 'country', 'prefix' => $prefix])
                ],
                'role' => [
                    'id' => $this->rl_id,
                    'int_id' => $this->rl_int_id,
                    'deleted' => (bool)$this->rl_deleted,
                    'links' => EntityLinkResource::make(['id' => $this->rl_id, 'entity' => 'role', 'prefix' => $prefix]),
                    'permissions' => json_decode($this->rl_permissions??'[]'),
                    'updated' => (bool)$this->rl_updated,
                ],
                'links' => EntityLinkResource::make(['id' => $this->e_id, 'entity' => 'employee', 'prefix' => $prefix]),
            ],
        ];
    }
}