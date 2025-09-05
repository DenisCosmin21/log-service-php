<?php

namespace Deniscosmin21\LogServicePhp\Facades;

use Deniscosmin21\LogServicePhp\LogData as Logger;

class LogData
{
    private static array $logger = [];

    public static function __callStatic($name, $arguments)
    {
        $log = new Logger(true);
        return $log->$name(...$arguments);
    }

    public static function toArray() : array
    {
        if (count(self::$logger) > 0) {
            return self::$logger;
        }
        return [];
    }

    public static function saveLog(array $log, string $key = 'main')
    {
        self::$logger[$key][] = $log;
    }
}

