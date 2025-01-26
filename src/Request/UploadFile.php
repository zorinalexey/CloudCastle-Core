<?php

namespace CloudCastle\Core\Request;

use CloudCastle\Core\Exceptions\StorageException;
use CloudCastle\Core\Storage\Disk;
use CloudCastle\Core\Storage\StorageInterface;
use function CloudCastle\Core\config;

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
            $this->disk = Disk::getDiskUsage($name, $disks[$name]['config']??[]);
        }
        
        return $this;
    }
}