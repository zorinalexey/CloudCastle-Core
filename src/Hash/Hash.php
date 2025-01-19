<?php

namespace CloudCastle\Core\Hash;

final class Hash
{
    public static function make(string $str): string
    {
        return md5($str);
    }
}