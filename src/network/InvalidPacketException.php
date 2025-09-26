<?php

namespace pocketmine\network;

class InvalidPacketException extends \RuntimeException
{
    public static function wrap(\Throwable $previous, ?string $prefix = null) : self{
        return new self(($prefix !== null ? $prefix . ": " : "") . $previous->getMessage(), 0, $previous);
    }
}
