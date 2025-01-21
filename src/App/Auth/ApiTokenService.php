<?php

namespace CloudCastle\Core\App\Auth;

use CloudCastle\Core\Filters\AuthApiFilter;
use CloudCastle\Core\Request\Request;
use CloudCastle\Core\Services\AbstractService;
use CloudCastle\Core\SqlBuilder\Insert;
use CloudCastle\Core\SqlBuilder\Update;
use DateTime;
use Ramsey\Uuid\Uuid;
use function CloudCastle\Core\app;

final class ApiTokenService extends AbstractService
{
    
    public function list (array $data): array
    {
        $cacheKey = AuthApiFilter::getTable() . ":collection:" . md5(json_encode([$data, __METHOD__]));
        
        $func = function () use ($data){
            [$sql, $binds] = AuthApiFilter::apply($data);
            
            return app()->db->paginate($sql, $binds, $data);
        };
        
        $before = $after = app()->cache->remember($cacheKey, $func);
        $this->setHistory($before, $after, 'collection');
        
        return $after;
    }
    
    public function view (string $token, string|null $user_agent = ''): array|null
    {
        $func = function () use ($token, $user_agent) {
            $request = Request::getInstance();
            $data = [
                'token' => $token,
                'user_agent' => $request->getAllHeaders()['user-agent']??'',
                'user_ip' => $request->getClientIp(),
                'trashed' => 'default',
            ];
            [$sql, $binds] = AuthApiFilter::apply($data);
            
            return app()->db->first($sql, $binds);
        };
        $data = $func();
        
        return $data;
    }
    
    public function create (array $data): array|null
    {
        $before = [];
        $data['id'] = (Uuid::uuid6())->toString();
        $data['created_at'] = (new DateTime())->format('Y-m-d H:i:s');
        [$sql, $binds] = Insert::into($data, AuthApiFilter::class);
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($data['id']);
            $after = $this->view($data['token'], $data['user_agent']);
            $this->setHistory($before, $after, 'create');
            
            return $after;
        }
        
        return null;
    }
    
    public function restore (string $id, string $action = 'restore'): array|null
    {
        $before = $this->view($id);
        $sql = "UPDATE " . AuthApiFilter::getTable() . " SET deleted_at = NULL, updated_at = now() WHERE id = :id";
        $binds = [':id' => $id];
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->clear();
            $after = $this->view($id);
            $this->setHistory($before, $after, $action);
            
            return $after;
        }
        
        return null;
    }
    
    public function softDelete (string $id, string $action = 'soft_delete'): bool
    {
        $before = $this->view($id);
        $sql = "UPDATE " . AuthApiFilter::getTable() . " SET deleted_at = now(), updated_at = now() WHERE id = :id";
        $binds = [':id' => $id];
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = $this->view($id);
            $this->setHistory($before, $after, $action);
        }
        
        return $result;
    }
    
    public function hardDelete (string $id, string $action = 'hard_delete'): bool
    {
        $before = $this->view($id);
        $sql = "DELETE FROM\n\t" . AuthApiFilter::getTable() . "\nWHERE id = :id";
        $binds = [':id' => $id];
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = [];
            $this->setHistory($before, $after, $action);
        }
        
        return $result;
    }
    
    public function restoreGroup (array $data): array
    {
        $result = [];
        
        foreach ($data as $item) {
            $result[$item] = $this->restore($item, 'restore_group');
        }
        
        return $result;
    }
    
    public function softDeleteGroup (array $data): array
    {
        $result = [];
        
        foreach ($data as $item) {
            $result[$item] = $this->softDelete($item);
        }
        
        return $result;
    }
    
    public function hardDeleteGroup (array $data): array
    {
        $result = [];
        
        foreach ($data as $item) {
            $result[$item] = $this->hardDelete($item);
        }
        
        return $result;
    }
    
    public function update (string $id, array $data): array|null
    {
        $data['updated_at'] = (new DateTime())->format('Y-m-d H:i:s');
        $before = $this->view($id);
        [$sql, $binds] = Update::set($data, AuthApiFilter::class, $id);
        $result = (bool) app()->db->query($sql, $binds)->rowCount();
        
        if ($result) {
            app()->cache->flush($id);
            $after = $this->view($id);
            $this->setHistory($before, $after, 'update');
            
            return $after;
        }
        
        return null;
    }
    
    public static function  generateToken(array $employee):array|null
    {
        $request = Request::getInstance();
        $dataToken = [
            'employee_id' => $employee['e_id'],
            'user_agent' => $request->getAllHeaders()['user-agent']??'',
            'user_ip' => $request->getClientIp(),
        ];
        
        $dataTokenAddParams['token'] = hash('sha512', md5(bin2hex(random_bytes(32))));
        $dataTokenAddParams['expires_at'] = date('Y-m-d H:i:s', strtotime('+'.((int)(app()->config->session['timeout']??15)).' minute'));
        [$sql, $binds] = AuthApiFilter::apply($dataToken);
        
        if($token = app()->db->first($sql, $binds))
        {
            return (new self())->update($token['id'], [...$dataToken, ...$dataTokenAddParams]);
        }
        
        return (new self())->create([...$dataToken, ...$dataTokenAddParams]);
    }
}