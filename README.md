Installation
```bash
composer require deniscosmin21/log-service-php
```

Customization
For custom options you should add to .env file :

```php
API_PASS_KEY=your_api_key
API_PASS_VALUE=your_api_value
ERROR_NAME=custom_path_to_error_log_file #basic is error.log
INFO_NAME=custom_path_to_info_log_file #basic is info.log
WARNING_NAME=custom_path_to_warning_log_file #basic is warning.log
SUCCESS_NAME=custom_path_to_success_log_file #basic is info.log
CUSTOM_PATH=custom_folder_for_logs #basic is /log_records
SOURCE=the_source_of_log
```
Usage

If you have a php project without laravel you have to use 
```php
require '../vendor/autoload.php';
```
to use the namespaces from composer packages

Initialization
```php
use Deniscosmin21\LogServicePhp\LogData;
```

Specify source

```php
use Deniscosmin21\LogServicePhp\LogData;

$logger = new LogData();

LogData::source('source');
```
This is not mandatory to do if you specified the source in the .env file

Specify type of log

You can either use the custom methods that feel like laravel
```php
use Deniscosmin21\LogServicePhp\LogData;

LogData::info() #for informational log

LogData::error() #for error log

LogData::warning() #for warning log

LogData::success() #for success log
```
You can even specify the message of the log inside
```php
LogData::info('my custom message');
```
Or you can use
```php
LogData::details('the_type_of_log_you_want', 'your_log_message');
```
#the first parameter is the type, and the second parameter are the details
For sending notification about a log you can use email method with the parameter a string of emails for example

"test@test.com,test2@test.com" "test@test.com"
```php
LogData::email('your_email_string_list_here');
```
Or you can send an array of emails for example
```php
LogData::email($your_email_array_list_here);
```
#the array should look like this : [(0) => 'first_email', (1) => 'second_email']
By using the email method you can specify as many emails as you want

For sending the notification to sms you can use
```php
LogData::sms('07....');
```
But keep in mind that this method makes the log to be sent to sms and to email so the usage should be like this
```php
LogData::email('emails')->sms('07....');
```
To use the api credentials you can either use the string for each value

```php
LogData::credentials('credentials_key', 'credentials_value');
```
Or you can use an array with the content : ['key' => 'credentials_key', 'value' => 'credentials_value']

```php
LogData::credentials(['key => 'credentials_key', 'value' => 'credentials_value']);
```
It's not mandatory to specify the credentials if you specified them inside the .env file

To send the log after setting all the information just use

```php
LogData::send();
```
Full usage example
```php
use Deniscosmin21\LogServicePhp\LogData;

return LogData::info('my_info_log')->source('source')->email('test@gmail.com')->credentials('key', 'value')->send();
```
Or if the .env file is all setted up
```php
use Deniscosmin21\LogServicePhp\LogData;

return LogData::info('my_info_log')->email('test@gmail.com')->send();
```
More informations
In case of error of the request the logger will return a response with the message specified, and the errors that it has. If any errors happen, the log will be registered locally in the path specified in the .env file or basic paths, with the body of the log : "[log_type log, in date : date_time_of_log] : Detalii : details; Locatie : location of the log.

The .env file should be stored in the root of the directory, else the app will fail
