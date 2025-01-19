<?php

namespace CloudCastle\Core\SqlBuilder;

use CloudCastle\Core\Filters\AbstractFilter;
use CloudCastle\Core\Traits\BindName;

final class Insert
{
    use BindName;
    
    public static function into (array $data, string $filter): array
    {
        $obj = new static();
        unset($data['int_id']);
        /** @var AbstractFilter $filter */
        $fields = $filter::getCrudFields();
        $sql = "INSERT INTO\n\t{$filter::getTable()} \n\t(\n\t\t";
        $intoFields = [];
        $intoValues = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $fields) && $value !== null) {
                $intoFields[] = $field;
                $intoValues[] = $obj->getBindName($value);
            }
        }
        
        $sql .= implode(",\n\t\t", $intoFields) . "\n\t)\n";
        $sql .= "VALUES\n\t(\n\t\t" . implode(",\n\t\t", $intoValues) . "\n\t)";
        
        return [$sql, $obj->getBinds()];
    }
}