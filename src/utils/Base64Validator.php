<?php

namespace pocketmine\utils;

use Exception;

class Base64Validator {
    private static string $BASE64_PATTERN = '/^[a-zA-Z0-9\/\+]*={0,2}$/';

    /**
     * @param $str
     * @return bool
     */
    public static function isBase64($str): bool
    {
        if (strlen($str) % 4 !== 0) {
            return false;
        }

        if (!preg_match(self::$BASE64_PATTERN, $str)) {
            return false;
        }

        try {
            return base64_decode($str, true) !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
