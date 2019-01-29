<?php

require_once('vendor/autoload.php');
require_once('sockets.php');
require_once('logs.php');

use Carbon\Carbon;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SearchEngine {

    const prs = [/*'WanderuProvider',*/ 'BusradarProvider'];

    private $p_towns;
    private $start;
    private $d;

    function __construct($p_towns, $start, $d = 1) {
        $this->p_towns = $p_towns;
        $this->start = $start;
        $this->d = $d;
    }

    function search($pr) {

        $provider = new $pr();

        foreach ($this->p_towns as $tn1 => $t1) {
            foreach ($this->p_towns as $tn2 => $t2) {
                if ($tn1 != $tn2) {
                    $st = $this->start->copy();
                    for ($t=0; $t < ($this->d+1); $t++, $st->addDay()) {

                        //echo $tn1 . ' ' . $tn2 . ' ' . $st->toDateString() . "\n";
                        //die(json_encode($t1));
                        $provider->routeTimetable($t1['coords'], $t2['coords'], $st);

                    }
                }
            }
        }

        return $provider->export();

    }

    function searchAll() {
        $response = array();
        foreach ( self::prs as $pr ) {
            $response += $this->search($pr);
        }
        return $response;
    }

    public static function searchRoute($t1, $t2, $st, $d = 1, $force = false) {
        $response = array();
        foreach ( self::prs as $pr ) {
            $provider = new $pr();
            for ($i=0; $i < $d; $i++, $st->addDay()) {
                $provider->routeTimetable($t1['coords'], $t2['coords'], $st, $force);
            }
            $rrr = $provider->export();
            $response += $rrr[2];
        }
        return $response;
    }

}


class DataProvider {

    protected $stations;
    protected $towns;
    protected $routes;

    function __construct() {
        $this->stations = array();
        $this->towns = array();
        $this->routes = array();
    }

    function export() {
        return [$this->stations, $this->towns, $this->routes];
    }

    public function routeTimetable($from, $to, $when, $again = false) {
        return ['sts' => array(), 'tns' => array(), 'rts' => array()];
    }

}



class WanderuProvider extends DataProvider {

    private static function getPlace($gid) {
        if (!($GLOBALS['cache']->R('getPlace', $gid))) {
            $GLOBALS["log"]->debug('getPlace of ' . $gid . ' from Internet', []);
           
            $r = json_decode(httpRetriever('https://api.wanderu.com/v2/maps/places.json?', ['place_id' => $gid, 'locale' => 'en-us', 'source' => 'google'])['body'], true)['result'];
            $GLOBALS['cache']->W('getPlace', $gid, $r);
            return $r;
    
        } else {
            //$GLOBALS["log"]->debug('getPlace of ' . $town . ' from Cache', [$GLOBALS['cache']->R('getPlace', $gid)]);
            return $GLOBALS['cache']->R('getPlace', $gid);
        }
    }

    public function routeTimetable($from, $to, $when, $again = false) {

        $ARGS = json_encode($from).'/'.json_encode($to).'/'.$when->toDateString();
    
        $GLOBALS["log"]->debug('wanderu.routeTimetable ' . ($again ? 'trying again...' : ''), [$ARGS.'/'.$again]);
    
        $result = $GLOBALS['cache']->R('wanderu.routeTimetable', $ARGS);
        if (($result !== false) && !($again)) {  $GLOBALS["log"]->debug('got from cache'); 
            
            $this->addSRC($from[3], $to[3], $result);
            return;
        
        } else {
            $GLOBALS["log"]->debug('requesting...');
        }
    
    
        $token = '';
    
        if ($again || !($GLOBALS['cache']->R('wanderu.routeTimetable.token', 'token'))) {
    
            $GLOBALS["log"]->debug('requesting token');
    
            $url = 'https://api.wanderu.com/v2/auth.json';
    
            $headers = array(
                'Content-Type'=> 'text/plain',
                'Origin'=> 'https://www.wanderu.com',
                'Host'=> 'api.wanderu.com',
                'Accept'=> 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language'=> 'en-us',
                'Connection'=> 'keep-alive',
                'User-Agent'=> 'Wanderu/140 CFNetwork/758.4.3 Darwin/15.5.0'    
            );
    
            $pre = json_decode(httpRetriever($url, [], $headers, ['body' => '{"credentials":{"type":"user","username":"anonymous","clientId":"1rtw1lMRWIkIheQflUD4EKJebYrVRwn_sn1MQs7T1QY","buildId":"3.11.116","clientName":"wanderu.web.search"},"meta":{"spNetworkID":"a94c5535-f959-4e7f-9308-937c130f45d6"},"options":{"httpStatusOk":true}}'], true)['body'], true);
            
            if (array_key_exists('error', $pre) === false) {
                $GLOBALS["log"]->error('"error" does not match the response format');
                if ($again) {
                    $GLOBALS["log"]->error('terminating routeTimetable');
                    F();
                } else {
                    $GLOBALS["log"]->debug('will try again');
                    usleep(1000);
                    return $this->routeTimetable($from, $to, $when, true);
                }
            }
    
            if ($pre['error'] == null) {
                $GLOBALS["log"]->debug('token response: ', [$pre['result']['token']]);
            } else {
                $GLOBALS["log"]->error('"error" does not match the response format');
                if ($again) {
                    $GLOBALS["log"]->error('terminating routeTimetable');
                    F();
                } else {
                    $GLOBALS["log"]->debug('will try again');
                    usleep(1000);
                    return $this->routeTimetable($from, $to, $when, true);
                }
            }
    
            $token = $pre['result']['token'];
            $GLOBALS['cache']->W('wanderu.routeTimetable.token', 'token', $token);
    
        } else {
    
            $token = $GLOBALS['cache']->R('wanderu.routeTimetable.token', 'token');
            $GLOBALS["log"]->debug('using token from cache', [$token]);
    
        }
    
            $curl = curl_init();
    
            $body = array();
            $body['originPlace'] = self::getPlace($from[2]);
            $body['destPlace'] = self::getPlace($to[2]);
            $body['departDatetime'] = $when->toDateString();
            //$ttt = json_decode('{"routed":true,"jitted":true,"cached":false,"tripFactorWeights":{"withinOriginSearchBounds":100,"withinOriginCityBounds":1,"withinDestSearchBounds":100,"withinDestCityBounds":1,"exactOriginMatch":200,"exactDestMatch":200,"distanceToOrigin":0,"distanceToDest":0,"travelDistance":0,"numberOfLegs":0,"travelDuration":100,"diversity":0.5},"stationFactorWeights":{"distance":1,"withinSearchBounds":1,"withinCityBounds":1,"exactMatch":100,"sharedCarrierRoute":1}}', true);
            $ttt = json_decode('{"routed":false,"jitted":false,"cached":true,"tripFactorWeights":{"withinOriginSearchBounds":100,"withinOriginCityBounds":1,"withinDestSearchBounds":100,"withinDestCityBounds":1,"exactOriginMatch":200,"exactDestMatch":200,"distanceToOrigin":0,"distanceToDest":0,"travelDistance":0,"numberOfLegs":0,"travelDuration":100,"diversity":0.5},"stationFactorWeights":{"distance":1,"withinSearchBounds":1,"withinCityBounds":1,"exactMatch":100,"sharedCarrierRoute":1}}', true);
            
            foreach ($ttt as $tk => $tv) {
                $body[$tk] = $tv;
            }
            $body['X-TOKEN'] = 'Bearer ' . $token;
            $body["batchId"] = "9a6f103e8a27b1449e21eb0478c1ed5841620dcb";

    
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.wanderu.com/v2/psearch.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_COOKIE => "__cfduid=dfcbb7a6d3099a56e317e75c7dc5480c61533591222",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json, text/javascript, */*; q=0.01",
                "accept-encoding: br, gzip, deflate",
                "accept-language: ru",
                "connection: keep-alive",
                "content-type: text/plain",
                "dnt: 1",
                "host: api.wanderu.com",
                "origin: https://www.wanderu.com",
                "user-agent: Wanderu/140 CFNetwork/758.4.3 Darwin/15.5.0"
            ),
            ));
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
    
            curl_close($curl);
    
            $r = json_decode($response, true);

    
        if ($r['error'] == null) {
            $GLOBALS["log"]->debug('search response is ok. will be cached');
            $GLOBALS['cache']->W('wanderu.routeTimetable', $ARGS, $r['result']);
        } else {
            if ($r['error']['code'] == '2103') {
                $GLOBALS["log"]->warning('routeTimetable: routes not found', [$r]);
                $GLOBALS['cache']->W('wanderu.routeTimetable', $ARGS, []);
                return array();
            } else {
                $GLOBALS["log"]->error('routeTimetable: error from api', [$r]);
                if ($again) {
                    $GLOBALS["log"]->error('terminating routeTimetable');
                    F();
                } else {
                    $GLOBALS["log"]->debug('will try again');
                    usleep(1000);
                    return $this->routeTimetable($from, $to, $when, true);
                }
            }
        }
    
        
        $result = $r['result'];

        $this->addSRC($from[3], $to[3], $result);
    
    }

    private function addSRC($tn1, $tn2, $src) {

        foreach ($src as $route) {

            if ($route['transfers'] === 0) {

                if (isset($route['itinerary_info']['itinerary'][0])) {
                    
                    $tt = $route['itinerary_info']['itinerary'][0];
                    if ((Problem::TOWN($tt['depart_cityname']) == $tn1) && (Problem::TOWN($tt['arrive_cityname']) == $tn2)) {

                        
                        $dS = $tt['depart_id'];
                        $aS = $tt['arrive_id'];
                        $dT = substr(Problem::TOWN($tt['depart_id']), 0, 5);
                        $aT = substr(Problem::TOWN($tt['arrive_id']), 0, 5);

                        $app = 'depart';
                        $this->stations[$dS] = new Station($dS, $tn1/*$route[$app . '_name']*/, $dT, $tt['vehicle_type']);
                        $this->towns[$dT] = new Town($dT, $tn1/*$route[$app . '_cityname']*/);
                        $app = 'arrive';
                        $this->stations[$aS] = new Station($aS, $tn2/*$route[$app . '_name']*/, $aT, $tt['vehicle_type']);
                        $this->towns[$aT] = new Town($aT, $tn2/*$route[$app . '_cityname']*/);
            
                        $this->routes[] = new Route($dS, $aS, Carbon::createFromTimestamp($tt['depart_datetime'], $tt['depart_timezone'])->toDateTimeString(), Carbon::createFromTimestamp($tt['arrive_datetime'], $tt['arrive_timezone'])->toDateTimeString(), $route['price'], $tn1, $tn2);
                        
                    }

                }

            }
    
        }

    }


}




//echo '<pre>';
//echo navitiaQuery('coverage/' . coordsForTown('Paris', 1) . '/routes', '');




class BusradarProvider extends DataProvider {

    public function routeTimetable($from, $to, $when, $again = false) {

        $ARGS = json_encode($from[3]).'/'.json_encode($to[3]).'/'.$when->toDateString();
        $result = $GLOBALS['cache']->R('busradar.routeTimetable', $ARGS);
        if (($result !== false) && !($again)) {  $GLOBALS["log"]->debug('got from cache'); 
            $this->addSRC($from[3], $to[3], $result);
            return;
        } else {
            $GLOBALS["log"]->debug('requesting...');
        }

        $client = new Client();
        $request = new Request(
        "GET",
        "http://195.110.58.57:3000/busradar/" . $when->toDateString() . "/" . $from[3] . "/" . $to[3],
        [],
        "");
        $result = json_decode($client->send($request)->getBody(), true);

        $GLOBALS['cache']->W('busradar.routeTimetable', $ARGS, $result);
        $this->addSRC($from[3], $to[3], $result);

    }

    private function addSRC($tn1, $tn2, $src) {

        foreach($src as $r) {

            if (count($r['Steps']) != 0) { continue; }
            if ($r['Price'] == 0) { continue; }

            $D = substr(substr($r['Departure'], 6), 0, -2);
            $A = substr(substr($r['Arrival'], 6), 0, -2);

                        $this->stations[$r['DepartureStop']] = new Station($r['DepartureStop'], $tn1, $r['DepartureCityId'], ($r['ConnectionType'] == 1) ? 'bus' : 'train');
                        $this->towns[$r['DepartureCityId']] = new Town($r['DepartureCityId'], $tn1/*$route[$app . '_cityname']*/);
                        $this->stations[$r['ArrivalStop']] = new Station($r['ArrivalStop'], $tn2, $r['ArrivalCityId'], ($r['ConnectionType'] == 1) ? 'bus' : 'train');
                        $this->towns[$r['ArrivalCityId']] = new Town($r['ArrivalCityId'], $tn2/*$route[$app . '_cityname']*/);
            
                        $this->routes[] = new Route($r['DepartureStop'], $r['ArrivalStop'], Carbon::createFromTimestampMs($D)->toDateTimeString(), Carbon::createFromTimestampMs($A)->toDateTimeString(), $r['Price'], $tn1, $tn2);

        }

    }




}


class HostelDataProvider {

    private $town;
    private $from;
    private $to;

    function __construct($town, $from, $to) {
        $this->town = $town;
        $this->from = $from;
        $this->to = $to;
        return $this;
    }

    public function searchAccomodation($p){

        $f = Carbon::createMidnightDate($this->from->year, $this->from->month, $this->from->day);
        $t = Carbon::createMidnightDate($this->to->year, $this->to->month, $this->to->day);
        $diff = $f->diffInDays($t);
        //return new HotelStay($this->town, $f, $t, 20.9);

        $ARGS = $this->from->toDateString().'/'.$this->to->toDateString().'/'.$this->town;
        $result = $GLOBALS['cache']->R('hostelbookers.search', $ARGS);
        if (($result !== false)) {  
            return new HotelStay($this->town, $f, $t, self::chooseRelevantPrice($result, $p) * $diff);
        }

        $client = new Client();
        $request = new Request(
                "GET",
                "https://www.hostelbookers.com/suggestions/?term=" . $this->town,
                [
                    "Host" => "www.hostelbookers.com",
                    "Dnt" => "1",
                    "Connection" => "keep-alive",
                    "Accept-Language" => "ru",
                    "Accept-Encoding" => "br, gzip, deflate",
                    "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15",
                    "Accept" => "application/json, text/javascript, */*; q=0.01",
                    "Referer" => "https://www.hostelbookers.com/",
                    "X-Requested-With" => "XMLHttpRequest"
                ],
        "");
        $response = $client->send($request);

        $townID = json_decode($response->getBody(), true)['suggestions'][0]['city']['id'];

        $client = new Client();
        $request = new Request(
                "GET",
                "https://www.hostelbookers.com/ajax/search/city/" . $townID . "/arr/" . $f->toDateString() . "/ngt/" . $diff . "/ppl/1/",
                [
                    "Host" => "www.hostelbookers.com",
                    "Dnt" => "1",
                    "Connection" => "keep-alive",
                    "Accept-Language" => "ru",
                    "Accept-Encoding" => "br, gzip, deflate",
                    "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15",
                    "Accept" => "application/json, text/javascript, */*; q=0.01",
                    "X-Requested-With" => "XMLHttpRequest"
                ],
                "");

        $r = json_decode($client->send($request)->getBody(), true);
        $GLOBALS['cache']->W('hostelbookers.search', $ARGS, $r);

        return new HotelStay($this->town, $f, $t, self::chooseRelevantPrice($r, $p) * $diff);

    }

    private static function chooseRelevantPrice($r, $p) {
        $rrr = $r['searchResults']['properties'];
        //return $r[0]['averageBedPricePerPersonPerNightLabel']['value'];

        $average_dorm = 0.0; $i_dorm = 0;
        $average_priv = 0.0; $i_priv = 0;

        foreach ($rrr as $r) {
            $dorm = 0;
            $priv = 0;
            if (count($r['dormRooms']) != 0) {
                foreach ($r['dormRooms'] as $room) {
                    $dorm += $room['averagePrice']['value'];
                }
                $dorm = $dorm / count($r['dormRooms']);
            }
            if (count($r['privateRooms']) != 0) {
                $PRIV_TEMP = 0;
                foreach ($r['privateRooms'] as $room) {
                    if ($room['capacity'] == 1) {
                        $PRIV_TEMP += 1;
                        $priv += $room['averagePrice']['value'];
                    }
                }
                if ($PRIV_TEMP != 0) {
                    $priv = $priv / $PRIV_TEMP;
                }
            }
            if ($priv != 0) {
                $average_priv += $priv; $i_priv += 1;
            }
            if ($dorm != 0) {
                $average_dorm += $dorm; $i_dorm += 1;
            }
        }

        if ($i_dorm != 0) {
            $average_dorm = $average_dorm / $i_dorm;
        }
        if ($i_priv != 0) {
            $average_priv = $average_priv / $i_priv;
        }
       

        if ($p->p_rules()['dorms']) {
            return $average_dorm;
        } else {
            return $average_priv;
        }

    }



}



?>