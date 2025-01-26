<?php

namespace CloudCastle\Core\Storage;

interface FileInterface
{
    public function create(string $path, string $content): bool;
    
    public function delete(string $path): bool;
    
    public function read(string $path): string;
    
    public function url(string $path): string;
}