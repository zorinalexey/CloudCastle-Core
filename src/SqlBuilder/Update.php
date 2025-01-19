<?php

namespace CloudCastle\Core\SqlBuilder;

use CloudCastle\Core\Filters\AbstractFilter;
use CloudCastle\Core\Traits\BindName;

final class Update
{
    use BindName;
    
    public static function set (array $data, string $filter, string $id): array
    {
        $obj = new static();
        unset($data['int_id']);
        /** @var AbstractFilter $filter */
        $fields = $filter::getCrudFields();
        $sql = "UPDATE\n\t{$filter::getTable()} \nSET\n\t";
        $updated = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $fields) && $value !== null) {
                $updated[] = "{$field} = " . $obj->getBindName($value);
            }
        }
        
        $sql .= implode(",\n\t", $updated) . "\nWHERE\n\tid = {$obj->getBindName($id)}\n";
        
        return [$sql, $obj->getBinds()];
    }
}