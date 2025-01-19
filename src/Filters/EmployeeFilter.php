<?php

namespace CloudCastle\Core\Filters;

use DateTime;

final class EmployeeFilter extends AbstractFilter
{
    
    protected string $deleted_field_name = 'e.deleted_at';
    protected string $updated_field_name = 'e.updated_at';
    protected string $created_field_name = 'e.created_at';
    protected string $name_field_name = 'e.name';
    protected string $id_field_name = 'e.id';
    protected string $int_id_field_name = 'e.int_id';
    
    public static function getTable (): string
    {
        return 'employees AS e';
    }
    
    public static function getFields (): array
    {
        return [
            'e.id AS e_id', 'e.int_id AS e_int_id', 'e.login AS e_login',
            'e.last_name AS e_last_name', 'e.name AS e_name', 'e.middle_name AS e_middle_name',
            "TRIM(CONCAT(e.int_id, ' - ', e.name, ' ', e.last_name, ' ', e.middle_name)) as full_name",
            'e.date_of_birth AS e_date_of_birth', 'e.virtual_number AS e_virtual_number', 'e.phone AS e_phone',
            'e.operator_group_id AS e_operator_group_id', 'e.external_id AS e_external_id', 'e.add_external_number AS e_add_external_number',
            'e.role_id AS e_role_id', 'e.city_id AS e_city_id', 'e.region_id AS e_region_id',
            'e.country_id AS e_country_id', 'e.is_blocked AS e_is_blocked', 'e.status AS e_status',
            'e.email AS e_email', 'e.password AS e_password', 'e.deleted_at AS e_deleted_at',
            'e.created_at AS e_created_at', 'e.updated_at AS e_updated_at', 'co.id AS co_id',
            'co.int_id AS co_int_id', 'co.name AS co_name', 'ct.id AS ct_id',
            'ct.int_id AS ct_int_id', 'ct.name AS ct_name', 'rg.id AS rg_id',
            'rg.int_id AS rg_int_id', 'rg.name AS rg_name', 'rl.id AS rl_id',
            'rl.int_id AS rl_int_id', 'rl.name AS rl_name', 'TEXT(rl.permissions) AS rl_permissions',
            'rl.default_page AS rl_default_page', 'rl.deleted AS rl_deleted', 'rl.updated AS rl_updated',
        ];
    }
    
    protected function sort_name(string $direction): void
    {
        $this->order[] = "full_name {$direction}";
    }
    
    protected function password(string $value): void
    {
        $this->condition[] = "AND e.password = {$this->getBindName($value)}";
    }
    
    protected function permissions(string $value): void
    {
        $this->condition[] = "AND LOWER(rl.permissions::TEXT) LIKE LOWER({$this->getBindName($value)}::TEXT)";
    }
    
    protected function country_name(string $value): void
    {
        $this->condition[] = "AND co.name = {$this->getBindName($value)}";
    }
    protected function city_name(string $value): void
    {
        $this->condition[] = "AND ct.name = {$this->getBindName($value)}";
    }
    protected function region_name(string $value): void
    {
        $this->condition[] = "AND rg.name = {$this->getBindName($value)}";
    }
    protected function role_name(string $value): void
    {
        $this->condition[] = "AND rl.name = {$this->getBindName($value)}";
    }
    
    protected function login(string $value): void
    {
        $bind = $this->getBindName($value);
        $this->condition[] = "AND (e.login = {$bind} OR e.email = {$bind} OR e.phone = {$bind})";
    }
    
    protected function last_name(string $value): void
    {
        $this->condition[] = "AND e.last_name = {$this->getBindName($value)}";
    }
    
    protected function middle_name(string $value): void
    {
        $this->condition[] = "AND e.middle_name = {$this->getBindName($value)}";
    }
    
    protected function date_of_birth(DateTime|string $value): void
    {
        if(is_string($value)) {
            $value = new DateTime($value);
        }
        
        $value = $value->format('Y-m-d');
        
        $this->condition[] = "AND DATE(e.date_of_birth) = DATE({$this->getBindName($value)})";
    }
    
    protected function virtual_number(string $value): void
    {
        $this->condition[] = "AND e.virtual_number = {$this->getBindName($value)}";
    }
    
    protected function phone(string $value): void
    {
        $this->condition[] = "AND e.phone = {$this->getBindName($value)}";
    }
    
    protected function operator_group_id(string $value): void
    {
        $this->condition[] = "AND e.operator_group_id = {$this->getBindName($value)}";
    }
    
    protected function external_id(string $value): void
    {
        $this->condition[] = "AND e.external_id = {$this->getBindName($value)}";
    }
    
    protected function add_external_number(string $value): void
    {
        $this->condition[] = "AND e.add_external_number = {$this->getBindName($value)}";
    }
    
    protected function role_id(string $value): void
    {
        $this->condition[] = "AND e.role_id = {$this->getBindName($value)}";
    }
    
    protected function city_id(string $value): void
    {
        $this->condition[] = "AND e.city_id = {$this->getBindName($value)}";
    }
    
    protected function region_id(string $value): void
    {
        $this->condition[] = "AND e.region_id = {$this->getBindName($value)}";
    }
    
    protected function country_id(string $value): void
    {
        $this->condition[] = "AND e.country_id = {$this->getBindName($value)}";
    }
    
    protected function is_blocked(string $value): void
    {
        $this->condition[] = "AND e.is_blocked = {$this->getBindName($value)}";
    }
    
    protected function status(string $value): void
    {
        $this->condition[] = "AND e.status = {$this->getBindName($value)}";
    }
    
    protected function e_email(string $value): void
    {
        $this->condition[] = "AND e.email = {$this->getBindName($value)}";
    }
    
    public static function getCrudFields (): array
    {
        return [
            'id', 'int_id', 'login',
            'last_name', 'name', 'middle_name',
            'date_of_birth', 'virtual_number', 'phone',
            'operator_group_id', 'external_id', 'add_external_number',
            'role_id', 'city_id', 'region_id',
            'country_id', 'is_blocked', 'status',
            'email', 'password', 'deleted_at',
            'created_at', 'updated_at',
        ];
    }
    
    public static function getGroupBy (): array
    {
        return [
            'e_id', 'e_int_id', 'e_login',
            'e_last_name', 'e_name', 'e_middle_name',
            'e_date_of_birth', 'e_virtual_number', 'e_phone',
            'e_operator_group_id', 'e_external_id', 'e_add_external_number',
            'e_role_id', 'e_city_id', 'e_region_id',
            'e_country_id', 'e_is_blocked', 'e_status',
            'e_email', 'e_password', 'e_deleted_at',
            'e_created_at', 'e_updated_at', 'co_id',
            'co_int_id', 'co_name', 'ct_id',
            'ct_int_id', 'rg_id', 'rg_int_id',
            'rg_name', 'rl_id', 'rl_int_id',
            'rl_name', 'rl_permissions', 'rl_default_page',
            'rl_deleted', 'rl_updated',
            'ct.name', 'ct.int_id', 'ct.name',
            'rg.name', 'rg.int_id', 'rg.name',
            'co.name', 'co.int_id', 'co.name',
            'rl.name', 'rl.int_id', 'rl.name',
        ];
    }
    
    public static function getJoins (): array
    {
        return [
            'LEFT JOIN countries AS co ON e.country_id = co.id',
            'LEFT JOIN cities AS ct ON e.city_id = ct.id',
            'LEFT JOIN regions AS rg ON e.region_id = rg.id',
            'LEFT JOIN roles AS rl ON e.role_id = rl.id',
        ];
    }
    
    public static function getSorts (): array
    {
        return [
        ];
    }
    
    public static function getFilters (): array
    {
        return [
        ];
    }
}