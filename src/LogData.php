<?php

namespace Deniscosmin21\LogServicePhp;

use Deniscosmin21\LogServicePhp\SendRequest;

class LogData
{

    private $source = '';
    private $type = '';
    private $details = '';
    private $send_notification = '';
    private $email_list = '';
    private $location = '';
    private $phone_number = '';
    private $credentials = ['key' => '', 'value' => ''];

    public function __construct($source = '')
    {
        $this->source($source);
        $location = debug_backtrace()[1];

        if(array_key_exists('class', $location)){
            $this->location = $this->location . $location['class'] . ' ';
        }

        $this->location = $this->location . $location['function'];

    }

    public function source($source)
    {
        $this->source = $source;
        return $this;
    }

    public function __call($name, $arguments)
    {
        if($name != 'credentials'){
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
        return $req->send_request($items);
    }
}
