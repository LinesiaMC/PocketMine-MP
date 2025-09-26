<?php

namespace pocketmine\utils\constants;

use pocketmine\network\mcpe\protocol\types\DeviceOS;

final class TitleId
{
    public const UNKNOWN = -1;
    public const ANDROID = "1739947436";
    public const IOS = "1810924247";
    public const OSX = -1;
    public const AMAZON = -1;
    public const GEAR_VR = -1;
    public const HOLOLENS = -1;
    public const WINDOWS_10 = "896928775";
    public const WIN32 = -1;
    public const DEDICATED = -1;
    public const TVOS = -1;
    public const PLAYSTATION = "2044456598";
    public const NINTENDO = "2047319603";
    public const XBOX = "1828326430";
    public const WINDOWS_PHONE = -1;

    /**
     * @param string $titleId
     * @return bool
     */
    public static function isValid(string $titleId): bool
    {
        //return true; //title id peut etre null depuis la 1.21.80
        return in_array($titleId, self::getValidIds(), true);
    }

    /**
     * @return string[]
     */
    private static function getValidIds(): array
    {
        return [self::ANDROID, self::IOS, self::WINDOWS_10, self::PLAYSTATION, self::NINTENDO, self::XBOX];
    }

    /**
     * @param string $titleId
     * @param int $deviceOs
     * @return bool
     */
    public static function equal(string|null $titleId, int $deviceOs): bool
    {
        if (is_null($titleId)) return false;

        $validIds = [
            DeviceOS::ANDROID => self::ANDROID,
            DeviceOS::IOS => self::IOS,
            DeviceOS::WINDOWS_10 => self::WINDOWS_10,
            DeviceOS::PLAYSTATION => self::PLAYSTATION,
            DeviceOS::XBOX => self::XBOX,
            DeviceOS::NINTENDO => self::NINTENDO,
        ];

        return isset($validIds[$deviceOs]) && $validIds[$deviceOs] === $titleId;
    }
}
