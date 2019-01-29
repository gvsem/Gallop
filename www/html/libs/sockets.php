<?php

/*function httpRetriever($url, $params, $post = false, $ssl = false, $agent = false) {

    $request = new \JJG\Request($url);
    if ($ssl) { $request->enableSSL(); }
    if ($agent !== false ) { $request->userAgent = $agent; }
    $request->connectTimeout = 5;
    $request->timeout = 10;
    $request->setRequestType($post ? 'POST' : 'GET');
    $request->setPostFields($params);
    $request->execute();
    $response = $request->getResponse();
    echo '<br><br>' . $request->getHeader() . '<br><br>';
    return $response;
}
 */

require_once('vendor/autoload.php');
use Carbon\Carbon;
use GuzzleHttp\Client;

function httpRetriever($url, $params = [], $headers = [], $other = [], $post = false, $output = false) {

    $client = new Client([
        // Base URI is used with relative requests
        'base_uri' => isset($headers['Host']) ? $headers['Host'] : $url,
        // You can set any number of default request options.
        'timeout'  => 2.0,
        'http_errors' => false
    ]);

    $GLOBALS["log"]->debug('httpRetriever: ' . $url, [$params]);

    try {

        $response = $client->request($post ? 'POST' : 'GET', $url, [
            'headers' => $headers,
            'query' => $params
        ] + $other);
    
    } catch (Exception $e) {

        $GLOBALS["log"]->warning('Code seems to be REALLY bad!');
        $output = true;

    }

    $c = $response->getStatusCode();
    $cr = $response->getReasonPhrase();
    $h = $response->getHeaders();
    $b = $response->getBody();

    if ((200 <= $c) && ($c <= 203) ) {} else {
        //$GLOBALS["log"]->error('httpRetriever: $url – ' . $c  . $cr, [$headers, $params, $response->getHeaders(), $response->getBody()]);
        $GLOBALS["log"]->warning('Code seems to be very bad!', [$c]);
        //$output = true;
        //return null;
    }

    if ($output) {
        $GLOBALS["log"]->debug('httpRetriever: ' . $url . ' – ' . $c  . $cr, [$params, $response->getBody()]);
    } else {
        $GLOBALS["log"]->debug('httpRetriever: ' . $url . ' – ' . $c  . $cr, [$params]);
    }

    return array(
            'code' => $c,
            'reason' => $cr,
            'headers' => $h,
            'body' => $b
        );

    
}


?>