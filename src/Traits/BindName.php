<?php

namespace CloudCastle\Core\Traits;

trait BindName
{
    protected array $binds = [];
    
    final public function getBindName (array|string|int|float|bool $value): array|string
    {
        if (is_bool($value)) {
            $value = $value ? 1 : 0;
        }
        
        if (!is_array($value)) {
            $name = ':bind_' . md5(serialize($value));
            $this->binds[$name] = $value;
            
            return $name;
        }
        
        $binds = [];
        
        foreach ($value as $v) {
            $binds[] = $this->getBindName($v);
        }
        
        return $binds;
    }
    
    public function getBinds (): array
    {
        return $this->binds;
    }
}