<?php

namespace CloudCastle\Core\Filters;

final class AuthApiFilter extends AbstractFilter
{
    
    public static function getTable (): string
    {
        return 'auth_tokens';
    }
    
    public static function getJoins (): array
    {
        return [];
    }
    
    public static function getFields (): array
    {
        return self::getCrudFields();
    }
    
    public static function getCrudFields (): array
    {
        return [
            'id',
            'int_id',
            'employee_id',
            'token',
            'user_agent',
            'user_ip',
            'expires_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
    
    protected function expires_at(string|int $id): void
    {
        $this->condition[] = "AND TIME(expires_at) >= TIME({$this->getBindName($id)})";
    }
    
    protected function employee_id(string|int $id): void
    {
        $this->condition[] = "AND employee_id = {$this->getBindName($id)}";
    }
    
    protected function token(string $token): void
    {
        $bind = $this->getBindName($token);
        $this->condition[] = "AND (token = {$bind} OR id = {$bind})";
    }
    
    protected function user_agent(string $user_agent): void
    {
        $this->condition[] = "AND user_agent = {$this->getBindName($user_agent)}";
    }
    
    protected function user_ip(string $user_ip): void
    {
        $this->condition[] = "AND user_ip = {$this->getBindName($user_ip)}";
    }
    
    public static function getGroupBy (): array
    {
        return [];
    }
    
    public static function getSorts (): array
    {
        return [];
    }
    
    public static function getFilters (): array
    {
        return [];
    }
}