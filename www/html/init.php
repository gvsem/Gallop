<?php

require_once('libs/autoload.php');

use Carbon\Carbon;
use Carbon\CarbonInterval;

$log->info('-----------------------------------');
$log->info('----------SESSION-STARTED----------');
$log->info('-----------------------------------');

const TIMEZONE = 'Europe/London';
ini_set('memory_limit', '-1');
date_default_timezone_set(TIMEZONE);
header('Content-type: text/plain');


/*$p = new BusradarProvider();
$p->routeTimetable(null,null,null);


die();*/

/*
    Checking that node.js server is running
*/

$old_path = getcwd();
chdir('/var/www/js/');
$output = shell_exec('node index.js');
chdir($old_path);


if (isset($_GET['data'])) {
    $data = json_decode($_GET['data'], true);
} else {
    $data = array(
        'towns' =>  array(
            'Warsaw' => [ 'mode' => 'start-end'],
            'Gdansk' => [   
                'min' => Problem::I('12h'),
                'max' => Problem::I('24h')
            ],
            'Berlin' => [   
                'min' => Problem::I('18h'),
                'max' => Problem::I('30h')
            ]   
        ),
        'rules' => array(
            'start' => Carbon::now()->addDays(1)->toDateTimeString() ,
            'lastTravelHour' => 0,
            'firstTravelHour' => 6,
            'dorms' => true,
            'advanced' => [
                'Price' => NULL,
                'MinTransportDuration' => NULL,
                /*'LessDayTrips' => NULL*/
            ]
        )
    );
    echo('WILL BE USING THIS CONDITION: ' . json_encode($data) . "\n\n\n");
}


$p = new Problem($data);

/*
    Try force pre-load timetable data
    $se = new SearchEngine($p->p_towns(), $p->start(), $p->estimatedTime());
    $p->import($se->searchAll());
*/

/*
    Print all found routes (if pre-loaded)
    foreach($p->routes() as $r) {
        echo $r->price() . ' | ' . $p->towns()[$p->stations()[$r->from()]->town()]->name() . ' -> ' . $p->towns()[$p->stations()[$r->to()]->town()]->name() . ' : ' . Carbon::parse($r->start())->toTimeString() . ' - ' . Carbon::parse($r->end())->toTimeString() . ' | ' . Carbon::parse($r->start())->format('l') . "\n";
    }
*/

$rws = Solver::solve($p);
var_dump($rws);

?>