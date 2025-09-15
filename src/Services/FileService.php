<?php

namespace Deniscosmin21\LogServicePhp\Services;

class FileService
{
    private static array $env = [];

    public function findFile(string $fileName): ?string
    {
        $path = dirname(__DIR__, 5);

        return $this->searchDirectory($path, $fileName);
    }

    public function searchDirectory(string $directory, string $fileName): ?string
    {
        $entries = scandir($directory);

        foreach ($entries as $entry) {
            if($entry == "." || $entry == "..")
                continue;

            if($entry == $fileName)
                return realpath($directory . DIRECTORY_SEPARATOR . $entry);

            if(is_dir($directory . DIRECTORY_SEPARATOR . $entry)){
                $result = $this->searchDirectory($directory . DIRECTORY_SEPARATOR . $entry, $fileName);
                if($result !== null){
                    return $result;
                }
            }

        }

        return null;
    }

    public function readEnvFile(?string $filePath): array
    {
        $env = [];
        if (!file_exists($filePath)) {
            return $env;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value,  "\n\r\t\v\0\"");
        }

        return $env;
    }

    public function getLogFile(string $type) : string
    {
        $mainPath = dirname(__DIR__, 5);

        $fileName = self::getEnv('LOG_' . strtoupper($type) . '_PATH');

        if($fileName == '')
            $fileName = "log_records/all.log";

        if(!is_dir($mainPath . DIRECTORY_SEPARATOR . 'log_records'))
            mkdir($mainPath . DIRECTORY_SEPARATOR . 'log_records', 0755, true);

        return $mainPath . DIRECTORY_SEPARATOR . $fileName;
    }

    public static function getEnv(string $key): string
    {
        return self::$env[$key] ?? '';
    }

    public static function setEnv(array $env): void
    {
        self::$env = $env;
    }
}