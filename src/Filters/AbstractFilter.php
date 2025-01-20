<?php

namespace CloudCastle\Core\Filters;

use CloudCastle\Core\Traits\BindName;
use DateTime;
use function CloudCastle\Core\trans;

abstract class AbstractFilter
{
    use BindName;
    
    protected string $table = '';
    protected array $condition = [];
    
    protected array $order = [];
    protected string $deleted_field_name = 'deleted_at';
    protected string $updated_field_name = 'updated_at';
    protected string $created_field_name = 'created_at';
    protected string $name_field_name = 'name';
    protected string $id_field_name = 'id';
    protected string $int_id_field_name = 'int_id';
    protected array $joins = [];
    
    final public static function apply (array $filters): array
    {
        $obj = new static();
        $obj->table = $obj->getTable();
        $obj->joins = $obj->getJoins();
        $obj->setDefaultSort($filters);
        
        foreach ($filters as $key => $filter) {
            if ($filter !== null && method_exists($obj, $key)) {
                $obj->{$key}($filter);
            }
        }
        
        $sql = "SELECT\n\t" . implode(",\n\t", $obj->getFields()) . "\nFROM\n\t{$obj->table}\n";
        
        if($obj->joins){
            $sql .= implode("\n", $obj->joins) . "\n";
        }
        
        $sql .= "WHERE\n\t" . trim(trim(implode("\n\t", $obj->condition), 'AND'), 'OR') . "\n";
        
        if ($fields = $obj->getGroupBy()) {
            $sql .= "GROUP BY\n\t" . implode(",\n\t", $fields) . "\n";
        }
        
        if ($obj->order) {
            $sql .= "ORDER BY\n\t" . implode(",\n\t", $obj->order) . "\n";
        }
        
        return [$sql."\n", $obj->binds];
    }
    
    abstract public static function getTable (): string;
    
    abstract public static function getJoins (): array;
    
    protected function setDefaultSort (array &$filters): void
    {
        if (!isset($filters['sort']) || empty($filters['sort'])) {
            if (in_array('name', $this->getAllFieldNames())) {
                $filters['sort']['name'] = 'ASC';
            } else {
                $filters['sort']['int_id'] = 'DESC';
            }
        }
    }
    
    final protected function getAllFieldNames (): array
    {
        $fields = [];
        
        foreach ([...$this->getFields(), ...$this->getCrudFields()] as $field) {
            $fields[] = preg_replace('~([\w]+) as ([\w]+)~ui', trim('$2'), $field);
        }
        
        return array_unique($fields);
    }
    
    abstract public static function getFields (): array;
    
    public static function getCrudFields (): array
    {
        $data = [];
        
        foreach(static::getFields() as $field) {
            if(!str_contains(mb_strtoupper($field), 'DISTINCT') && !str_contains(mb_strtoupper($field), 'AS')) {
                $data[] = $field;
            }
        }
        
        return $data;
    }
    
    abstract public static function getGroupBy (): array;
    
    abstract public static function getSorts (): array;
    
    abstract public static function getFilters (): array;
    
    final protected static function getCommonSorts (): array
    {
        $options = [
            'asc' => trans('sorts', 'asc'),
            'desc' => trans('sorts', 'desc'),
            '' => trans('sorts', 'default'),
        ];
        
        return [
            [
                'id' => 'sort[id]',
                'label' => trans('sorts', 'sort.id'),
                'type' => 'select',
                'options' => $options,
            ],
            [
                'id' => 'sort[int_id]',
                'label' => trans('sorts', 'sort.int_id'),
                'type' => 'select',
                'options' => $options,
            ],
            [
                'id' => 'sort[deleted_at]',
                'label' => trans('sorts', 'sort.deleted_at'),
                'type' => 'select',
                'options' => $options,
            ],
            [
                'id' => 'sort[created_at]',
                'label' => trans('sorts', 'sort.created_at'),
                'type' => 'select',
                'options' => $options,
            ],
            [
                'id' => 'sort[updated_at]',
                'label' => trans('sorts', 'sort.updated_at'),
                'type' => 'select',
                'options' => $options,
            ]
        ];
    }
    
    final protected function search (string $search): void
    {
        $values = explode(' ', $search);
        $filters = [];
        
        foreach ($this->getFields() as $key => $field) {
            foreach ($values as $i => $value) {
                if($field = $this->getFieldNameBySearch($field)){
                    $filters["{$key}_{$i}"] = "LOWER({$field}::TEXT) LIKE LOWER({$this->getBindName("%{$value}%")})";
                }
            }
        }
        
        $sql = "SELECT\n\t\t\t" . implode(",\n\t\t\t", $this->getSearchFields()) . "\n\t\tFROM\n\t\t\t{$this->table}\n\t\t\t";
        $sql .= implode("\n\t\t\t", $this->joins) . "\n\t\t";
        $sql .= "WHERE\n\t\t\t" . implode("\n\t\t\tOR ", $filters) . "\n\t\t";
        
        if ($fields = $this->getGroupBy()) {
            $sql .= "GROUP BY\n\t\t\t" . implode(",\n\t\t\t", $fields);
        }
        
        $this->table = "(\n\t\t{$sql}\n\t) ".preg_replace('~([\w.]+) AS (\w+)~ui', 'AS $2', $this->table)?:' as subquery';
    }
    
    protected function getFieldNameBySearch (string $fieldName): string|null
    {
        $patterns = [
            '~([\w\.]+) AS (\w+)~ui' => '$1',
            '~([\w]+)~ui' => '$1',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $fieldName) && !str_contains(mb_strtoupper($fieldName), 'DISTINCT')) {
                return preg_replace($pattern, $replacement, $fieldName);
            }
        }
        
        return null;
    }
    
    final protected function trashed (string $trashed): void
    {
        $value = match (mb_strtolower($trashed)) {
            'only', 'trashed' => "AND {$this->deleted_field_name} IS NOT NULL",
            'default' => "AND {$this->deleted_field_name} IS NULL",
            default => null,
        };
        
        if ($value) {
            $this->condition[] = $value;
        }
    }
    
    final protected function id (string $id): void
    {
        $this->condition[] = "AND {$this->id_field_name} = {$this->getBindName($id)}";
    }
    
    final protected function int_id (int $id): void
    {
        $this->condition[] = "AND {$this->int_id_field_name} = {$this->getBindName($id)}";
    }
    
    final protected function name (string $name): void
    {
        $this->condition[] = "AND {$this->name_field_name} = {$this->getBindName($name)}";
    }
    
    final protected function deleted_at (DateTime $date): void
    {
        $this->condition[] = "AND DATE({$this->deleted_field_name}) = DATE({$this->getBindName($date->format('Y-m-d'))})";
    }
    
    final protected function updated_at (DateTime $date): void
    {
        $this->condition[] = "AND DATE({$this->updated_field_name}) = DATE({$this->getBindName($date->format('Y-m-d'))})";
    }
    
    final protected function created_at (DateTime $date): void
    {
        $this->condition[] = "AND DATE({$this->created_field_name}) = DATE({$this->getBindName($date->format('Y-m-d'))})";
    }
    
    final protected function sort (array $sorts): void
    {
        $fields = $this->getAllFieldNames();
        
        foreach ($sorts as $name => $direction) {
            $method = "sort_{$name}";
            $direction = mb_strtoupper($direction);
            
            if ($direction !== 'DESC') {
                $direction = 'ASC';
            }
            
            if (method_exists($this, $method)) {
                $this->{$method}($direction);
            } elseif (in_array($name, $fields)) {
                $this->order[] = "{$name} {$direction}";
            }
        }
    }
    
    private function getSearchFields (): array
    {
        $fields = [];
        
        foreach ($this->getFields() as $field) {
            if(!str_contains($field, 'DISTINCT')) {
                $fields[] = $field;
            }
        }
        
        return $fields;
    }
}