<?php

require_once('vendor/autoload.php');
require_once('sockets.php');
require_once('logs.php');

use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class Geo {

    public static function length($town1, $town2) {
        return $this->haversineGreatCircleDistance($town1->latitude(), $town1->longitude(), $town2->latitude(), $town2->longitude());
    }

    private static function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
      
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
      
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public static function deprecatedCoordsOpen($town, $string = 0, $again = false) {

        $GLOBALS["log"]->debug('coordsOpen ' . ($again ? 'trying again...' : ''), [$town]);
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL,"https://nominatim.openstreetmap.org/search?format=json&q=" . $town);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: TravelAgentByMrGeorgeous'
        ));
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if (is_array($result) === false) {
        
            if ($again) {
                $GLOBALS["log"]->fatal('coordsOpen failed too. terminating', [$town]);
                F();
            } else {
                $GLOBALS["log"]->error('Nominatim Connection error. Trying again...', [$town]);
                usleep(1000);
                return self::coordsOpen($town, $string, true);
            }
        
        }
        
        $r = [$result[0]['lon'], $result[0]['lat'], null, $town];
        if ($string) {
            $r = implode(';', $r);
        }
        return $r;

    }

    public static function coordsGoogle($town, $string = 0, $again = false) {
        
        $httpClient = new \Http\Adapter\Guzzle6\Client();
        $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient);
        $geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');
        
        $GLOBALS["log"]->debug('coordsGoogle ' . ($again ? 'trying again...' : ''), [$town]);
        
        try {
            //var_dump(($geocoder->geocodeQuery(GeocodeQuery::create($town)))->first()); die();
            $r = ($geocoder->geocodeQuery(GeocodeQuery::create($town)));
            $r = [$r->first()->getCoordinates()->getLongitude(), $r->first()->getCoordinates()->getLatitude(), $r->first()->getId(), $town];
            if ($string) {
                $r = implode(';', $r);
            }
            return $r;
            
        } catch (Exception $e) {
            if ($again) {
                $GLOBALS["log"]->fatal('coordsGoogle failed too. terminating', [$town]);
                F();
            } else {
                $GLOBALS["log"]->error('Google Connection error. Trying again... ', [$town]);
                usleep(1000);
                return self::coordsGoogle($town, $string, true);
            }
        }
        
    }

    public static function coords($town, $string = 0) {
        if (!($GLOBALS['cache']->R('coords', $town . $string))) {
            $GLOBALS["log"]->debug('coords of ' . $town . ' from Internet', [$town]);
            $data = self::coordsGoogle($town, $string);
            $GLOBALS['cache']->W('coords', $town . $string, $data);
            return $data;
        } else {
            //$GLOBALS["log"]->debug('coords of ' . $town . ' from Cache', [$GLOBALS['cache']->R('coords', $town . $string)]);
            return $GLOBALS['cache']->R('coords', $town . $string);
        }
    }

}


?>