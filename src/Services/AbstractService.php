<?php

namespace CloudCastle\Core\Services;

use CloudCastle\Core\Auth\Auth;
use Exception;
use Guzzle\Http\Client;
use function CloudCastle\Core\app;

abstract class AbstractService
{
    protected array $errors = [];
    
    public function getErrors (): array
    {
        return $this->errors;
    }
    
    abstract public function list (array $data): array;
    
    abstract public function view (string $id, string|null $trashed = 'all'): array|null;
    
    abstract public function create (array $data): array|null;
    
    abstract public function restore (string $id): array|null;
    
    abstract public function softDelete (string $id): bool;
    
    abstract public function hardDelete (string $id): bool;
    
    abstract public function restoreGroup (array $data): array;
    
    abstract public function softDeleteGroup (array $data): array;
    
    abstract public function hardDeleteGroup (array $data): array;
    
    abstract public function update (string $id, array $data): array|null;
    
    protected function setHistory(array|null $before, array|null $after, string $action): void
    {
        $setting = app()->config->history[$action]??false;
        $url = app()->config->history['url']??false;
        
        if(!$before){
            $before = [];
        }
        
        if(!$after){
            $after = [];
        }
        
        if($setting && $url) {
            $id = $before['id']??null;
            
            if(!$id){
                $id = $after['id'] ?? null;
            }
            
            $data = [
                'before' => json_encode($before),
                'after' => json_encode($after),
                'action' => $action,
                'class' => self::class,
                'entity' => app()->env->entity,
                'entity_id' => $id,
                'employee_id' => Auth::user()->id?:'system',
            ];
            
            $client = new Client();
            $token = Auth::user()->token;
            
            try{
                $client->post($url, [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json'
                    ]
                ]);
            }catch (Exception $e){
            
            }
        }
    }
}