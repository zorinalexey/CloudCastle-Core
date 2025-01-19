<?php

namespace CloudCastle\Core\Resources;

use function CloudCastle\Core\app;

/**
 * @property string $entity
 * @property string $prefix
 */
abstract class AbstractResource
{
    private array|null|object $data = null;
    
    final protected function __construct (array|object|null $data = null)
    {
        $this->data = $data;
        
        foreach($this->data??[] as $key => $value) {
            $this->{$key} = $value;
        }
        
        if(!$this->entity){
            $this->entity = app()->env->entity;
        }
        
        if(!$this->prefix){
            $this->prefix = trim(app()->env->api_prefix, '/');
        }
    }
    
    final public static function collection (array|null $data = null): array
    {
        $collection = [];
        
        if ($data === null) {
            return $collection;
        }
        
        foreach ($data as $object) {
            $collection[] = self::make($object);
        }
        
        return array_values($collection);
    }
    
    final public static function make (array|object|null $data = null): array
    {
        $object = new static($data);
        
        foreach ($data??[] as $key => $value) {
            $object->{$key} = $value;
        }
        
        $result = [];
        
        foreach ($object->toArray() as $name => $value) {
            $result[$name] = $value;
        }
        
        unset($object);
        
        return $result;
    }
    
    abstract public function toArray (): array;
    
    public function __get (string $name): mixed
    {
        if ($this->data === null) {
            return null;
        }
        
        if (is_object($this->data)) {
            return $this->data->{$name} ?? null;
        }
        
        return $this->data[$name] ?? null;
    }
}