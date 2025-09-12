<?php

namespace Deniscosmin21\LogServicePhp\Services;

use Deniscosmin21\LogServicePhp\Repositories\RequestRepository;

class RequestService
{
    private RequestRepository $repository;
    private FileService $fileService;

    /**
     * @param RequestRepository $repository
     */
    public function __construct(RequestRepository $repository, FileService $fileService)
    {
        $this->repository = $repository;
        $this->fileService = $fileService;
    }

    public function sendLogs(array $logsCategories)
    {
        if(count($logsCategories) == 0)
            return;

        foreach($logsCategories as $key => $logs) {
            if(count($logs) == 0)
                continue;

            if($key == 'main') {
                 $resp = $this->repository->sendLogs($logs, FileService::getEnv('API_KEY'), FileService::getEnv('API_PASSWORD'));
            }
            else {
                $password = $logs[0]['credentials']['value'];
                $resp = $this->repository->sendLogs($logs, $key, $password);
            }

            if($resp['code'] != 200 || $resp['error'] != '' || isset($resp['response']['errors'])) {
                $errors = [];

                if($resp['error'] != '')
                    $errors = $this->buildServerErrorResponse($resp['error'], count($logs));
                else if(isset($resp['response']['errors']))
                    $errors = $resp['response']['errors'];


                $this->saveFailedLogs($logs, $errors);
            }
        }
    }

    private function saveFailedLogs(array $logs, array $errors)
    {
        foreach($errors as $idLog => $error) {
            $message = $this->createLogMessage($logs[$idLog]);
            $this->repository->saveFailedLog($message, $error, $this->fileService->getLogFile($logs[$idLog]['type']));
        }
    }

    private function createLogMessage($log) : string
    {
        $date = date('Y-m-d');
        $time = date('h:i:sa');

        $message = '[' . strtoupper($log['type']) . ' log, in date : ' . $date . ' ' . $time . ']';
        $message = $message . ' : Detalii : ' . $log['details'] . ' Locatie : ' . $log['location'];

        return $message;
    }

    private function buildServerErrorResponse(string $error, int $count) : array
    {
        $errors = [];

        for($i = 0; $i < $count; $i++) {
            $errors[$i] = $error;
        }

        return $errors;
    }
}