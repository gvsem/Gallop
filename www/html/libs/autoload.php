<?php

require_once('vendor/autoload.php');
require_once('misc/Request.php');
require_once('misc/websocket_client.php');
require_once('misc/SSEClient.php');
require_once('logs.php');
require_once('cache.php');
require_once('geo.php');
require_once('sockets.php');
require_once('structs.php');
require_once('timetables.php');
require_once('rules.php');



header("HTTP/1.1 200 OK");
ini_set('display_errors', '1');

?>