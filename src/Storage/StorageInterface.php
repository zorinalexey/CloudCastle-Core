<?php

namespace CloudCastle\Core\Storage;

interface StorageInterface
{
    public function file(): FileInterface;
    public function dir(): DirInterface;
    public function link(): LinkInterface;
}