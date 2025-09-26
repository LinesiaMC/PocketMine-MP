<?php

namespace pocketmine\utils;

class HexChecker
{
    /**
     * @param $str
     * @return bool
     */
    public static function isHexadecimal($str): bool
    {
        $regex = '/^[0-9A-Fa-f]+$/';
        return preg_match($regex, $str) === 1;
    }
}