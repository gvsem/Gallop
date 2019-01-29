<?php

require_once('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

global $log;

// Common logger
$log = new Logger('Solver');
// Line formatter without empty brackets in the end
$formatter = new LineFormatter(null, null, false, true);
// Debug level handler
$debugHandler = new StreamHandler('logs/debug.log', Logger::DEBUG);
$debugHandler->setFormatter($formatter);
// Error level handler
$errorHandler = new StreamHandler('logs/error.log', Logger::ERROR);
$errorHandler->setFormatter($formatter);
// This will have both DEBUG and ERROR messages
$log->pushHandler($debugHandler);
// This will have only ERROR messages
$log->pushHandler($errorHandler);
// The actual logging
//$log->debug('I am debug');
//$log->error('I am error', array('productId' => 123));

function F() {
    die('Terminated because of fatal error. See debug.log');
}

file_put_contents(realpath(__DIR__ . '/..') . '/logs/debug.log', '');

?>