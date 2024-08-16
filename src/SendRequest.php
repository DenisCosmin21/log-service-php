<?php

namespace Deniscosmin21\LogServicePhp;

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

class SendRequest
{

    private $env = null;

    public function send_request($items)
    {
        if($items['source'] == ''){
            $items['source'] = $this->env_get('SOURCE');
        }

        if($items['credentials']['key'] == '' || $items['credentials']['value'] == ''){
            if($this->exists_env() == true){
                $items['credentials'] = ['key' => $this->env_get('API_PASS_KEY'), 'value' => $this->env_get('API_PASS_VALUE')];
            }
            else{
                $this->write_to_log_file($items);
            }
        }

        $client = new Client([
            'auth' => [$items['credentials']['key'], $items['credentials']['value']]
        ]);

        $headers = [
          'Content-Type' => 'application/x-www-form-urlencoded',
          'Authorization' => 'Basic'
        ];


        $options = [
        'form_params' => [
          'source' => $items['source'],
          'type' => $items['type'],
          'location' => $items['location'],
          'details' => $items['details'],
          'send_notification' => $items['send_notification'],
          'email_list' => $items['email_list'],
          'phone_number' => $items['phone_number']
        ]];

        try{
            $res = $client->request('POST', 'https://logs.mezoni.ro/api/send_log', $options);
            return $res->getBody()->getContents();
        }
       catch(RequestException $e)
        {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            if($statusCode >= 400){
                $this->write_to_log_file($items, $body);
            }
            return $body;
        }
    }

    private function get_log_file_path($type)
    {
        $file_path = dirname(__DIR__, 4);

        $name_by_type = [
            'error' => 'all.log',
            'success' => 'all.log',
            'warning' => 'all.log',
            'info' => 'all.log',
        ];
        
        $file_path = ($this->env_get('CUSTOM_PATH') == '' ? $file_path : $this->env_get('CUSTOM_PATH'));

        $name_by_type['error'] = ($this->env_get('ERROR_NAME') == '' ? $name_by_type['error'] : $this->env_get('ERROR_NAME') . '.log');
        $name_by_type['info'] = ($this->env_get('INFO_NAME') == '' ? $name_by_type['info'] : $this->env_get('INFO_NAME') . '.log');
        $name_by_type['warning'] = ($this->env_get('WARNING_NAME') == '' ? $name_by_type['warning'] : $this->env_get('WARNING_NAME') . '.log');
        $name_by_type['success'] = ($this->env_get('SUCCESS_NAME') == '' ? $name_by_type['success'] : $this->env_get('SUCCESS_NAME') . '.log');

        $check_name = explode('.', $name_by_type[$type]);
        if(count($check_name) > 2){
            $count = array_count_values($check_name);
            if($count['log'] > 1){
                array_pop($check_name);
                $name_by_type[$type] = implode('.', $check_name);
            }
        }

        if($file_path == dirname(__DIR__, 4)){
            $file_path = $file_path . '/log_records';
        }

        if(!is_dir($file_path)){
            mkdir($file_path, 0755, true);
        }

        return fopen($file_path . DIRECTORY_SEPARATOR . $name_by_type[$type], 'a');
    }

    private function make_message($items)
    {
        $date = date('Y-m-d');
        $time = date('h:i:sa');

        $message = '[' . strtoupper($items['type']) . ' log, in date : ' . $date . ' ' . $time . ']';
        $message = $message . ' : Detalii : ' . $items['details'] . 'Locatie : ' . $items['location'];

        return $message;
    }

    private function write_to_log_file($items, $body = null)
    {
        $type = $items['type'];

        $file = $this->get_log_file_path($type);
        
        if($file){
            $message = $this->make_message($items);
            fwrite($file, $message . PHP_EOL);
            if($body != null){
                fwrite($file, '[Server error response] : ' . $body . PHP_EOL);
            }
            fclose($file);
        }
    }

    private function exists_env()
    {
        if($this->env == null){
            $this->env = Dotenv::createImmutable(dirname(__DIR__, 4));
        }

        return (count($_ENV) != 0);
    }

    private function env_get($name)
    {
        if($this->exists_env()){
            if(array_key_exists($name, $_ENV)){
                return $_ENV[$name];
            }
        }
        return '';
    }

}
