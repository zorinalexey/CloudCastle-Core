<?php

namespace CloudCastle\Core\Storage;

use CloudCastle\Core\Exceptions\StorageException;
use function CloudCastle\Core\config;

final class Disk
{
    public static function getDiskUsage(string $diskName, array $diskConfig): StorageInterface
    {
        $disk = config('filesystem')[$diskName]??null;
        
        if(class_exists($disk['class']) && ($obj = new ($disk['class'])(...$diskConfig)) && $obj instanceof StorageInterface){
            return $obj;
        }
        
        throw new StorageException("Disk [$diskName] not configured");
    }
}