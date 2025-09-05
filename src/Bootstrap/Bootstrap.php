<?php

namespace Deniscosmin21\LogServicePhp\Bootstrap;

use Deniscosmin21\LogServicePhp\Facades\LogData;
use Deniscosmin21\LogServicePhp\Repositories\RequestRepository;
use Deniscosmin21\LogServicePhp\Services\FileService;
use Deniscosmin21\LogServicePhp\Services\RequestService;

class Bootstrap
{
    public static function registerShutdownFunction()
    {
        register_shutdown_function(function() {
            $requestService = new RequestService(new RequestRepository(), new FileService());
            $requestService->sendLogs(LogData::toArray());
        });
    }

    public static function saveEnv()
    {
        $fileService = new FileService();
        $path = $fileService->findFile('.env');
        FileService::setEnv($fileService->readEnvFile($path));
    }
}

Bootstrap::registerShutdownFunction();
Bootstrap::saveEnv();