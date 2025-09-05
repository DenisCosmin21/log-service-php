<?php

namespace Deniscosmin21\LogServicePhp;

use Deniscosmin21\LogServicePhp\SendRequest;
use Deniscosmin21\LogServicePhp\Services\FileService;

class LogData
{

    private string $source = '';
    private string $type = '';
    private string $details = '';
    private string $send_notification = 'false';
    private string $email_list = '';
    private string $location = '';
    private string $phone_number = '';
    private array $credentials = [];

    public function __construct()
    {
        $this->source = FileService::getEnv('SOURCE');

        $location = debug_backtrace();
        if(array_key_exists(2, $location)){
            $location_data = $location[2];
            if(array_key_exists('class', $location_data)){
                $this->location = $this->location . $location_data['class'];
            }
    
            $this->location = $this->location . ' ' . $location_data['function'] . ' on line : ' . $location[1]['line'];
        }
        else{
            $location = debug_backtrace()[1];
            $this->location = $location['file'] . ' on line : ' . $location['line'];
        }
    }

    public function __call($name, $arguments)
    {
        if($name != 'credentials'){

            $details = '';
            $type = '';

            if(count($arguments) == 0){
                $details = '';
            }
            else{
                $details = $arguments[0];
            }

            if($name == 'info' || $name == 'Info'){
                $type = 'info';
            }
            else if($name == 'error' || $name == 'Error')
            {
                $type = 'error';
            }
            else if($name == 'warning' || $name == 'Warning')
            {
                $type = 'warning';
            }
            else if($name == 'success' || $name == 'Success')
            {
                $type = 'success';
            }

            return $this->details($type, $details);
        }
        else{
            if(gettype($arguments[0]) == 'array'){
                $this->credentials = $arguments[0];
            }
            else{
                $this->credentials = ['key' => $arguments[0], 'value' => $arguments[1]];
            }
            return $this;
        }
    }

    public function source($source)
    {
        $this->source = $source;
        return $this;
    }

    public function details($type = 'info', $details = '')
    {
        $this->type = $type;
        $this->details = $details;
        return $this;
    }

    public function email($email_list)
    {
        $this->send_notification = 'email';
        if(gettype($email_list) == 'string'){
            $this->email_list = $email_list;
        }
        else{
            $this->email_list = implode(',', $email_list);
        }

        return $this;
    }

    public function sms($phone_number)
    {
        $this->send_notification = 'email_and_sms';
        $this->phone_number = $phone_number;

        return $this;
    }

    public function toArray() : array
    {
        return [
            'source' => $this->source,
            'type' => $this->type,
            'details' => $this->details,
            'send_notification' => $this->send_notification,
            'email_list' => $this->email_list,
            'location' => $this->location,
            'phone_number' => $this->phone_number,
            'credentials' => $this->credentials
        ];
    }

    public function __destruct()
    {
        if(isset($this->credentials['key']) && isset($this->credentials['value'])){
            \Deniscosmin21\LogServicePhp\Facades\LogData::saveLog($this->toArray(), $this->credentials['key']);
        }
        else
        {
            \Deniscosmin21\LogServicePhp\Facades\LogData::saveLog($this->toArray());
        }
    }
}

