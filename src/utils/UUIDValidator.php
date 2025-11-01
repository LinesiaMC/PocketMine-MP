<?php

namespace pocketmine\utils;

use Exception;
use InvalidArgumentException;

class UUIDValidator {
	private const UUID_PATTERN =
		'/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

	// Base64 strict (multiple de 4 + padding)
	private const BASE64_PATTERN =
		'/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/';

	/**
	 * @param string $uuid
	 * @return bool
	 */
	public static function isValidUuid(string $v): bool
	{
		return \preg_match(self::UUID_PATTERN, $v) === 1;
	}

	public static function isValidGdkDeviceId(string $v): bool
	{
		// la plupart de ceux qu’on voit font 44 chars (= 32 bytes en base64)
		return \preg_match(self::BASE64_PATTERN, $v) === 1
			&& \strlen($v) >= 28   // marge
			&& \strlen($v) <= 128;
	}

	public static function isValidAny(string $v): bool
	{
		return self::isValidUuid($v) || self::isValidGdkDeviceId($v);
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
