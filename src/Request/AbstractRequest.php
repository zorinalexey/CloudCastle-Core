<?php

namespace CloudCastle\Core\Request;

use CloudCastle\Core\Exceptions\ValidateException;
use DateMalformedStringException;
use DateTime;
use stdClass;
use function CloudCastle\Core\{config, trans};

abstract class AbstractRequest extends stdClass
{
    protected static array $common = [
        'sort' => 'array|nullable',
        'trashed' => 'string|nullable',
        'search' => 'string|nullable',
        'per_page' => 'int|nullable',
        'page' => 'int|nullable',
        'id' => 'string|nullable',
        'int_id' => 'int|nullable',
        'deleted_at' => 'date|nullable',
        'created_at' => 'date|nullable',
        'updated_at' => 'date|nullable',
    ];
    public int $page = 1;
    public int $per_page = 100;
    public string $trashed = 'default';
    private array $errors = [];
    
    final public function __construct ()
    {
        $config = config('app');
        $this->page = (int) $config['page'] ?? 1;
        $this->per_page = (int) $config['per_page'] ?? 100;
        
        foreach (Request::getInstance() as $property => $value) {
            $this->{$property} = $value;
        }
    }
    
    final public function validated (): array
    {
        $data = [];
        
        foreach ($this->rules() as $property => $rule) {
            if (property_exists($this, $property) || str_contains($rule, 'required')) {
                $this->validate($property, $rule);
                $data[$property] = &$this->{$property};
            } else {
                $data[$property] = null;
            }
            
            if (str_contains($rule, 'nullable') && !$this->{$property}) {
                unset($this->errors[$property]);
            }
        }
        
        if (count($this->errors) > 0) {
            $str = trans('validation', 'Validation failed').': ';
            $key = 0;
            foreach ($this->errors as $error) {
                $key ++;
                $str .= "{$key}) {$error}. ";
            }
            
            throw new ValidateException(trim($str), 10025);
        }
        
        return $data;
    }
    
    abstract public function rules (): array;
    
    private function validate (int|string $property, mixed $rule): void
    {
        $rules = explode('|', $rule);
        
        foreach ($rules as $rule) {
            if (method_exists($this, $rule)) {
                $this->$rule($property);
            }
        }
    }
    
    final public function __get (string $property): mixed
    {
        return $this->{$property} ?? null;
    }
    
    private function string (string $property): void
    {
        if (is_string($this->{$property}) || is_int($this->{$property}) || is_float($this->{$property})) {
            $this->{$property} = trim($this->{$property});
        } else {
            $this->errors[$property] = trans('validation', 'must be a string', [':entity' => $property]);
        }
    }
    
    private function int (string $property): void
    {
        if (is_numeric($this->{$property}) || is_int($this->{$property})) {
            $this->{$property} = (int) $this->{$property};
        } else {
            $this->errors[$property] = trans('validation', 'is not a valid integer', [':entity' => $property]);
        }
    }
    
    private function float (string $property): void
    {
        if (is_numeric($this->{$property}) || is_float($this->{$property})) {
            $this->{$property} = (float) $this->{$property};
        } else {
            $this->errors[$property] = trans('validation', 'is not a float', [':entity' => $property]);
        }
    }
    
    private function required (string $property): void
    {
        if (!$this->{$property}) {
            $this->errors[$property] = trans('validation', 'is required', [':entity' => $property]);
        }
    }
    
    private function boolean (string $property): void
    {
        $this->bool($property);
    }
    
    private function bool (string $property): void
    {
        $this->{$property} = filter_var($this->{$property}, FILTER_VALIDATE_BOOLEAN);
    }
    
    private function email (string $property): void
    {
        if ($value = filter_var($this->{$property}, FILTER_VALIDATE_EMAIL) !== false) {
            $this->{$property} = $value;
        } else {
            $this->errors[$property] = trans('validation', 'is not a email', [':entity' => $property]);
        }
    }
    
    
    private function url (string $property): void
    {
        if ($value = filter_var($this->{$property}, FILTER_VALIDATE_URL) !== false) {
            $this->{$property} = $value;
        } else {
            $this->errors[$property] = trans('validation', 'is not a url', [':entity' => $property]);
        }
    }
    
    /**
     * @throws DateMalformedStringException
     */
    private function date (string $property): void
    {
        if ($value = strtotime($this->{$property})) {
            $this->{$property} = new DateTime(date('Y-m-d H:i:s', $value));
        } else {
            $this->errors[$property] = trans('validation', 'is not a date', [':entity' => $property]);
        }
    }
    
    private function array (string $property): void
    {
        if (isset($this->{$property}) && !is_array($this->{$property})) {
            $this->errors[$property] = trans('validation', 'is not an array', [':entity' => $property]);
        }
    }
    
    /**
     * @throws DateMalformedStringException
     */
    private function timestamp (string $property): void
    {
        if ($value = strtotime($this->{$property})) {
            $this->{$property} = (new DateTime(date('Y-m-d H:i:s', $value)))->getTimestamp();
        } elseif (is_numeric($this->{$property})) {
            $this->{$property} = (new DateTime(date("Y-n-d H:i:s", (int) $this->{$property})))->getTimestamp();
        } else {
            $this->errors[$property] = trans('validation', 'is not a date or time', [':entity' => $property]);
        }
    }
}