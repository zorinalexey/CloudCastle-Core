<?php

namespace CloudCastle\Core\Request;

use CloudCastle\Core\Exceptions\StorageException;
use CloudCastle\Core\Storage\StorageInterface;
use function CloudCastle\Core\{config, getDiskUsage};

class UploadFile
{
    public readonly string $name;
    public readonly string $type;
    public readonly string $size;
    public readonly string $tmp_name;
    public readonly string $error;
    private StorageInterface $disk;
    
    public function __construct (array $file)
    {
        foreach ($file as $key => $value) {
            if(isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
        
        $this->storage('local');
    }
    
    /**
     * @throws StorageException
     */
    public function storage(string $name): self
    {
        $disks = config('filesystem')['disks'];
        
        if(isset($disks[$name])) {
            $this->disk = getDiskUsage($name, $disks[$name]['config']??[]);
        }
        
        return $this;
    }
    
    public function save(string $path): string
    {
        return $this->disk->file()->create($path, file_get_contents($this->tmp_name));
    }
}