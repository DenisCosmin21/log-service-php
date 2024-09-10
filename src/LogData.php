<?php

namespace Deniscosmin21\LogServicePhp;

use Deniscosmin21\LogServicePhp\SendRequest;

class Logger
{

    private $source = '';
    private $type = '';
    private $details = '';
    private $send_notification = 'false';
    private $email_list = '';
    private $location = '';
    private $phone_number = '';
    private $credentials = ['key' => '', 'value' => ''];
    private $sent = 0;

    public function __construct($is_from_static = false)
    {
        $id = 1;
        if($is_from_static)
        {
            $id = 2;
        }
        
        $location = debug_backtrace();
        if(array_key_exists((string)$id, $location)){
            $location_data = $location[$id];
            if(array_key_exists('class', $location_data)){
                $this->location = $this->location . $location_data['class'];
            }
    
            $this->location = $this->location . ' ' . $location_data['function'] . ' on line : ' . $location[$id - 1]['line'];
        }
        else{
            $location = debug_backtrace()[$id - 1];
            $this->location = $location['file'] . ' on line : ' . $location['line'];
        }
    }

    public function source($source)
    {
        $this->source = $source;
        return $this;
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

    public function send()
    {
        $items = ['source' => $this->source, 'type' => $this->type, 'location' => $this->location, 'details' => $this->details, 'send_notification' => $this->send_notification, 'email_list' => $this->email_list, 'phone_number' => $this->phone_number, 'credentials' => $this->credentials];

        $req = new SendRequest();

        $this->sent = 1;

        return $req->send_request($items);
    }

    public function __destruct()
    {
        if($this->sent == 0){
            $this->send();
        }
    }
}

class LogData
{
    public static function __callStatic($name, $arguments)
    {
        $log = new Logger(true);
        return $log->$name(...$arguments);
    }    
}
