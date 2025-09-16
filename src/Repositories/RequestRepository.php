<?php

namespace Deniscosmin21\LogServicePhp\Repositories;

class RequestRepository
{
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init("http://logservice-env-1.eba-wtnqyrgy.eu-central-1.elasticbeanstalk.com/api/save_logs");
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
    }

    public function sendLogs(array $logs, string $key, string $password) : array
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, "$key:$password");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($logs));

        $response = curl_exec($this->curl);

        return ['code' => curl_getinfo($this->curl, CURLINFO_HTTP_CODE), 'response' => json_decode($response, true), 'error' => curl_error($this->curl)];
    }

    public function saveFailedLog(string $message, string $error, string $location)
    {
        $file = fopen($location, "a");

        fwrite($file, $message);
        fwrite($file, '[Server error response] : ' . $error . PHP_EOL);

        fclose($file);
    }
}