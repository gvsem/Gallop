<?php

require '../vendor/autoload.php';

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Respond 200 OK with an optional
 * This is used to return an acknowledgement response indicating that the request has been accepted and then the script can continue processing
 *
 * @param null $text
 */
function respondOK($text = null)
{
    // check if fastcgi_finish_request is callable
    if (is_callable('fastcgi_finish_request')) {
        if ($text !== null) {
            echo $text;
        }
        /*
         * http://stackoverflow.com/a/38918192
         * This works in Nginx but the next approach not
         */
        session_write_close();
        fastcgi_finish_request();
 
        return;
    }
 
    ignore_user_abort(true);
 
    ob_start();
 
    if ($text !== null) {
        echo $text;
    }
 
    $serverProtocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
    header($serverProtocol . ' 200 OK');
    // Disable compression (in case content length is compressed).
    header('Content-Encoding: none');
    header('Content-Length: ' . ob_get_length());
 
    // Close the connection.
    header('Connection: close');
 
    ob_end_flush();
    ob_flush();
    flush();


    sleep(2);

}

//respondOK();

//$GLOBALS["log"]->debug('woohoo!');

$msg = $_GET['msg'];
$token = $_GET['token'];



$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, 'https://www.busradar.com/api2/signalr/send?transport=longPolling&clientProtocol=1.5&culture=en&currency=EUR&connectionToken=' . urlencode($token) . '&connectionData=%5B%7B%22name%22:%22searchhub%22%7D%5D');

// set method
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

// return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// set headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Cookie: CookieDetector=; _ga=GA1.2.1517559395.1533252451; _gid=GA1.2.952200896.1534070833; _gat=1',
  'Origin: https://www.busradar.com',
  'X-Language: en',
  'Accept-Encoding: gzip, deflate, br',
  'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
  'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
  'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
  'Accept: text/plain, */*; q=0.01',
  'Referer: https://www.busradar.com/search/?From=Moscow&To=Saint+Petersburg&When=2018-08-13&WhenReturn=0001-01-01&Passengers=1&Radius=15000&WithChangeover=true',
  'X-Requested-With: XMLHttpRequest',
  'Connection: keep-alive',
]);

// form body

echo $msg . PHP_EOL;
echo urldecode($msg) . PHP_EOL;

$body = [
  'data' => urldecode($msg),
];
$body = http_build_query($body);

// set body
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

// send the request and save response to $response
$response = curl_exec($ch);

// stop if fails
if (!$response) {
  die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

echo 'HTTP Status Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
echo 'Response Body: ' . $response . PHP_EOL;

// close curl resource to free up system resources 
curl_close($ch);










die();
//$GLOBALS["log"]->debug('yrap!');
$client = new Client();

        $request = new Request(
                "POST",
                "https://www.busradar.com/api2/signalr/send?transport=longPolling&clientProtocol=1.5&culture=en&currency=EUR&connectionToken=" . urlencode($token) . "&connectionData=%5B%7B%22name%22:%22searchhub%22%7D%5D",
                [
                    "Cookie" => "CookieDetector=; _ga=GA1.2.1517559395.1533252451; _gid=GA1.2.952200896.1534070833; _gat=1",
                    "Origin" => "https://www.busradar.com",
                    "X-Language" => "en",
                    "Accept-Encoding" => "gzip, deflate, br",
                    "Accept-Language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
                    "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
                    "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
                    "Accept" => "text/plain, */*; q=0.01",
                    "Referer" => "https://www.busradar.com/search/?From=Moscow&To=Saint+Petersburg&When=2018-08-13&WhenReturn=0001-01-01&Passengers=1&Radius=15000&WithChangeover=true",
                    "X-Requested-With" => "XMLHttpRequest",
                    "Connection" => "keep-alive"
                ],
                "data=" . urlencode($msg)
            );

        $resp = $client->send($request);

        var_dump($resp);
        //echo $resp;
        //$GLOBALS["log"]->debug('sending.. !!! done');

       // $GLOBALS["log"]->debug($resp);
        

?>