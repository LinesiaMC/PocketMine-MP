<?php

namespace pocketmine\utils;

use Exception;
use InvalidArgumentException;

class UUIDValidator {
    private static string $UUID_PATTERN = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';


    /**
     * @param string $uuid
     * @return bool
     */
    public static function isValidUUID(string $uuid): bool
    {
        return preg_match(self::$UUID_PATTERN, $uuid) === 1;
    }

    /**
     * @param string $uuid
     * @return int|mixed
     */
    public static function getUUIDVersion(string $uuid): mixed
    {
        try {
            return self::fromString($uuid)['version'];
        } catch (Exception $e) {
            return -1;
        }
    }

    /**
     * @param string $uuidString
     * @return array
     */
    private static function fromString(string $uuidString): array
    {
        if (!self::isValidUUID($uuidString)) {
            throw new InvalidArgumentException("Invalid UUID string: $uuidString");
        }

        $parts = explode('-', $uuidString);
        $timeLow = hexdec($parts[0]);
        $timeMid = hexdec($parts[1]);
        $timeHiAndVersion = hexdec($parts[2]);

        $version = ($timeHiAndVersion >> 12) & 0x0F;

        return [
            'time_low' => $timeLow,
            'time_mid' => $timeMid,
            'time_hi_and_version' => $timeHiAndVersion,
            'version' => $version
        ];
    }
}
