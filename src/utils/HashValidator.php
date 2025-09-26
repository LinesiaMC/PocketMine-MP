<?php

namespace pocketmine\utils;

class HashValidator
{
    private static string $MD5_PATTERN = '/^[a-fA-F0-9]{32}$/';
    private static string $SHA256_PATTERN = '/^[a-fA-F0-9]{64}$/';

    /**
     * @param string $hash
     * @return bool
     */
    public static function isValidHash(string $hash): bool
    {
        return self::isValidMD5($hash) || self::isValidSHA256($hash);
    }

    /**
     * @param string $hash
     * @return bool
     */
    public static function isValidMD5(string $hash): bool
    {
        return preg_match(self::$MD5_PATTERN, $hash) === 1;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public static function isValidSHA256(string $hash): bool
    {
        return preg_match(self::$SHA256_PATTERN, $hash) === 1;
    }
}