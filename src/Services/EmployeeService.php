<?php

namespace CloudCastle\Core\Services;

use CloudCastle\Core\Exceptions\AuthException;
use CloudCastle\Core\Filters\EmployeeFilter;
use CloudCastle\Core\Hash\Hash;
use CloudCastle\Core\SqlBuilder\Insert;
use CloudCastle\Core\SqlBuilder\Update;
use DateTime;
use Ramsey\Uuid\Uuid;
use function CloudCastle\Core\{app, trans};

final class EmployeeService extends AbstractService
{
    
    public function list (array $data): array
    {
        $func = function () use ($data){
            $data['password'] = Hash::make($data['password']);
            [$sql, $binds] = EmployeeFilter::apply($data);
            $employee = app()->db->paginate($sql, $binds, $data)??null;
            
            if($employee){
                return $employee;
            }
            
            throw new AuthException(trans('auth', 'failed'), 10250);
        };
        
        $before = $after = $func();
        $this->setHistory($before, $after, 'collection');
        
        return $after;
    }
    
    public function findOne (array $data): array
    {
        $func = function () use ($data){
            $data['password'] = Hash::make($data['password']);
            [$sql, $binds] = EmployeeFilter::apply($data);
            $employee = app()->db->first($sql, $binds)??null;
            
            if($employee){
                return $employee;
            }
            
            throw new AuthException(trans('auth', 'failed'), 10250);
        };
        
        $before = $after = $func();
        $this->setHistory($before, $after, 'collection');
        
        return $after;
    }
    
    public function findAll (array $data): array
    {
        $func = function () use ($data){
            $data['password'] = Hash::make($data['password']);
            [$sql, $binds] = EmployeeFilter::apply($data);
            $employee = app()->db->get($sql, $binds)??null;
            
            if($employee){
                return $employee;
            }
            
            throw new AuthException(trans('auth', 'failed'), 10250);
        };
        
        $before = $after = $func();
        $this->setHistory($before, $after, 'collection');
        
        return $after;
    }
    
    
    
    public function create (array $data): array|null
    {
        $before = [];
        $data['id'] = (Uuid::uuid6())->toString();
        $data['created_at'] = (new DateTime())->format('Y-m-d H:i:s');
        [$sql, $binds] = Insert::into($data, EmployeeFilter::class);
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($data['id']);
            $after = $this->view($data['id']);
            $this->setHistory($before, $after, 'create');
            
            return $after;
        }
        
        return null;
    }
    
    public function view (string $id, string|null $trashed = 'all'): array|null
    {
        $data = ['id' => $id];
        $data['trashed'] = $trashed;
        $cacheKey = EmployeeFilter::getTable() . ":id:" . md5(json_encode($data));
        
        $func = function () use ($data){
            [$sql, $binds] = EmployeeFilter::apply($data);
            
            return app()->db->first($sql, $binds);
        };
        
        $before = $after = app()->cache->remember($cacheKey, $func);
        $this->setHistory($before, $after, 'view');
        
        return $after;
    }
    
    public function restoreGroup (array $data): array
    {
        $result = [];
        
        foreach ($data['ids'] as $id) {
            $result[$id] = $this->restore($id, 'restore_group');
        }
        
        return $result;
    }
    
    public function restore (string $id, string $action = 'restore'): array|null
    {
        $before = $this->view($id);
        $sql = "UPDATE " . EmployeeFilter::getTable() . " SET deleted_at = NULL, updated_at = now() WHERE id = :id";
        $binds = [':id' => $id];
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = $this->view($id);
            $this->setHistory($before, $after, $action);
            
            return $after;
        }
        
        return null;
    }
    
    public function softDeleteGroup (array $data): array
    {
        $result = [];
        
        foreach ($data['ids'] as $id) {
            $result[$id] = $this->softDelete($id, 'soft_delete_group');
        }
        
        return $result;
    }
    
    public function softDelete (string $id, string $action = 'soft_delete'): bool
    {
        $before = $this->view($id);
        $sql = "UPDATE " . EmployeeFilter::getTable() . " SET deleted_at = now(), updated_at = now() WHERE id = :id";
        $binds = [':id' => $id];
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = $this->view($id);
            $this->setHistory($before, $after, $action);
        }
        
        return $result;
    }
    
    public function hardDeleteGroup (array $data): array
    {
        $result = [];
        
        foreach ($data['ids'] as $id) {
            $result[$id] = $this->hardDelete($id, 'hard_delete_group');
        }
        
        return $result;
    }
    
    public function hardDelete (string $id, string $action = 'hard_delete'): bool
    {
        $before = $this->view($id);
        $sql = "DELETE FROM\n\t" . EmployeeFilter::getTable() . "\nWHERE id = :id";
        $binds = [':id' => $id];
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = [];
            $this->setHistory($before, $after, $action);
        }
        
        return $result;
    }
    
    public function update (string $id, array $data): array|null
    {
        $data['updated_at'] = (new DateTime())->format('Y-m-d H:i:s');
        $before = $this->view($id);
        [$sql, $binds] = Update::set($data, EmployeeFilter::class, $id);
        
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = $this->view($id);
            $this->setHistory($before, $after, 'update');
            
            return $after;
        }
        
        return null;
    }
}