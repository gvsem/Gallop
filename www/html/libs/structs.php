<?php

require_once('vendor/autoload.php');
require_once('rules.php');
use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * 
 * Class which contains:
 * 1. problem description itself (town set & rules)
 * 2. stations, towns, routes etc. – data retrieved from Web
 *    to be used by solver
 *
 */

class Problem {

    private $problem = array();

    private $stations = array();
    private $towns = array();
    private $routes = array();

    public function stations() { return $this->stations; }
    public function towns() { return $this->towns; }
    public function routes() { return $this->routes; }

    // SOLVER PROPERTIES
    private $solve_towns = array();
    public function solve_towns() { return $this->solve_towns; }
    // END SOLVER PROPERTIES

    function __construct($data) {

        // Initing problem data & data for solver
        $this->problem = $data;
        $this->solve_towns = $this->p_towns();

        // Calculating coords for all towns
        foreach ($this->problem['towns'] as $town => $t) {
            $this->problem['towns'][$town]['coords'] = Geo::coords($town);
        }

    }

    function import($e) {
        $this->stations = $this->stations + $e[0];
        $this->towns = $this->towns + $e[1];
        $this->routes = $this->routes + $e[2];

        usort($this->routes, function($a, $b) { return $a->start()->timestamp - $b->start()->timestamp; });
    }

    public function p_towns() {return $this->problem['towns'];}
    public function p_rules() {return $this->problem['rules'];}

    public static function I($t) {return call_user_func_array(array("Carbon\CarbonInterval", "make"), func_get_args())->spec();}
    public static function TOWN($t) { $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή'); $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η'); return str_replace($a, $b, $t); }
    private static function S($obj, $key, $value) { if (is_array($value)) {} else {$value = [$value];} foreach ($value as $Gv) {foreach ($obj as $k => $v) {if ($v[$key] == $Gv) {return $k;}}} $GLOBALS["log"]->fatal('S. Search in object failed.', [$obj,$key,$value]); F();}
    
    public function tnStart() {
        return self::tn(self::S($this->problem['towns'],'mode',['start-end','start']));
    }

    public function tnEnd() {
        return self::tn(self::S($this->problem['towns'],'mode',['start-end','end']));
    }

    public function tnStartName() {
        return self::S($this->problem['towns'],'mode',['start-end','start']);
    }

    public function tnEndName() {
        return self::S($this->problem['towns'],'mode',['start-end','end']);
    }

    public function tn($name) {
        foreach ($this->towns as $tn) {
            if (self::TOWN($tn->name()) === self::TOWN($name)) {
                return $tn;
            }
        }
    }

    public function estimatedTime() {
        $sum = CarbonInterval::create('PT0H');
        foreach ($this->p_towns() as $town => $t) {
            if (isset($t['max'])) { $sum->add(CarbonInterval::create($t['max'])); continue; }
            if (isset($t['min'])) { $sum->add(CarbonInterval::create($t['min'])); continue; }
        }
        return $sum->cascade()->dayz;
    }

    public function start() {
        return Carbon::parse($this->p_rules()['start']);
    }

    public function routesOf($tn1, $tn2, $start, $end) {
        $response = array();

        //echo $tn1 . ' ' . $tn2 . ' ' . $start->toDateTimeString() . ' -> ' . $end->toDateTimeString() . PHP_EOL;

        $at_least_one_end_day = false;

        foreach ($this->routes as $r) {

            //echo ($this->towns[$this->stations[$r->from()]->town()])->name() . ' / ' . $tn1 . "\n";
            //echo ($this->towns[$this->stations[$r->to()]->town()])->name() . ' / ' . $tn2 . "\n";

            if (($this->towns[$this->stations[$r->from()]->town()]->name() == $tn1)
                &&
                ($this->towns[$this->stations[$r->to()]->town()]->name() == $tn2)
                &&
                ($r->start() >= $start)
                &&
                ($r->start() <= $end)) {
                    $response[] = $r;
                    if ($r->start()->day == $end->day) {
                        $at_least_one_end_day = true;
                    }
            }

        }


        if (((count($response) == 0) || !($at_least_one_end_day))) {
            //echo ' ';
            $d = 1 + $start->diffInDays($end);

            //$GLOBALS["log"]->debug($tn1 . ' ' . $tn2 . ' ' . $start->toDateTimeString() . ' -> ' . $end->toDateTimeString());


            //echo $d . ' ll ';
            //$se = new SearchEngine([$this->p_towns()[$tn1], $this->p_towns()[$tn2]], $start, $d);
            $ss = SearchEngine::searchRoute($this->p_towns()[$tn1], $this->p_towns()[$tn2], $start->copy(), $d);
            //print_r($ss);
            foreach ($ss as $r) {
                //echo $r->start()->toDateTimeString() . PHP_EOL;
                //echo $r->start()->toDateTimeString() . ' ' . $start->toDateTimeString() . PHP_EOL;
                //var_dump(($r->start()->greaterThanOrEqualTo($start)));
                //var_dump(($end->greaterThanOrEqualTo($r->start())));
                if (($r->start()->greaterThanOrEqualTo($start)) && ($end->greaterThanOrEqualTo($r->start()))) {
                    $response[] = $r;
                }
    
            }

        } else {
            //print_r($response);
        }
        //print_r($response);
        return $response;
    }

}

class Town {

    private $name = 'Town';
    private $id = 'CNTWN';
    private $coordinates = [1.00000,1.00000]; // lng, lat

    public function name() { return $this->name; }
    public function latitude() { return $this->coordinates[1]; }
    public function longitude() { return $this->coordinates[0]; }
    public function coords() { return $this->coordinates; }

    function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
        $this->coordinates = Geo::coords($name);
    }

}

class Station {

    private $id = 'UNIQUE ID GIVEN BY DATA DISTRIBUTOR'; // depart_id / arrive_id
    private $name = 'STATION NAME PROPERTY'; // depart_name / arrive_name
    private $town = 'TOWN ID PROPERTY'; // depart_wcityid / arrive_wcityid
    private $type = 'bus';

    function __construct($id, $name, $town, $type) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->town = $town;
    }

    function id() { return $this->id; }
    function name() { return $this->name; }
    function town() { return $this->town; }
    function type() { return $this->type; }

}

class Route {

    private $st1;
    private $st2;
    private $d_time;
    private $a_time;
    private $price;
    private $from_tn_name;
    private $to_tn_name;

    function __construct($st1, $st2, $d_time, $a_time, $price, $from_tn_name, $to_tn_name) {
        $this->st1 = $st1;
        $this->st2 = $st2;
        $this->d_time = $d_time;
        $this->a_time = $a_time;
        $this->price = $price;
        $this->from_tn_name = $from_tn_name;
        $this->to_tn_name = $to_tn_name;
    }

    function from() { return $this->st1; }
    function to() { return $this->st2; }
    function start() { return Carbon::parse($this->d_time); }
    function end() { return Carbon::parse($this->a_time); }
    function price() { return $this->price; }

    function fromTnName() { return $this->from_tn_name; }
    function toTnName() { return $this->to_tn_name; }
    function durationInHours() { return $this->start()->diffAsCarbonInterval($this->end())->totalHours; }

    function dayDurationInHours($args) {

        $rules = array('lastTravelHour' => $args[0], 'firstTravelHour' => $args[1]);
        $start = $this->start()->toDateTimeString();
        $end = $this->end()->toDateTimeString();

        if ($this->isStartNight($rules)) {
            $this->d_time = $this->actualStartMid($rules)->addDays(1)->addHours($args[1])->toDateTimeString();
        } else {
            $this->d_time = $this->d_time;
        }

        if ($this->isEndNight($rules)) {
            if ($args[0] > $args[1]) { // 23 - 6
                $this->a_time = $this->actualEndMid($rules)->subDays(1)->addHours($args[0])->toDateTimeString();
            }
            if ($args[0] < $args[1]) { // 0 - 6
                $this->a_time = $this->actualEndMid($rules)->addHours($args[0])->toDateTimeString();
            }
        }

        $this->eq_start = $this->d_time;
        $this->eq_end = $this->a_time;

        if ($this->actualStart($rules)->toDateString() == $this->actualEnd($rules)->toDateString()) {
            $return = $this->durationInHours();
        } else {
            $return = $this->durationInHours() - ($this->actualStartMid($rules)->diffAsCarbonInterval($this->actualEndMid($rules))->dayz) * self::nightDuration($rules);
        }

        $this->d_time = $start;
        $this->a_time = $end;

        return $return;
       
    }

    function isStartNight($rules) { return $this->isDateNight($this->start(), $rules); }
    function isEndNight($rules) { return $this->isDateNight($this->end(), $rules); }

    function actualStart($rules) {
        $lastTravelHour = $rules['lastTravelHour']; // 0
        $firstTravelHour = $rules['firstTravelHour']; // 6
        $hour = $this->start()->hour;
        if ($this->isStartNight($rules) === false) { return $this->start(); }
        if (($hour >= 0) && ($hour <= $firstTravelHour)) {
            return $this->start()->copy()->subDays(1);
        } else { return $this->start(); }
    }

    function actualStartMid($rules) { return self::dateMidnight($this->actualStart($rules));}
    function actualEndMid($rules) { return self::dateMidnight($this->actualEnd($rules));}

    function actualEnd($rules) {
        $lastTravelHour = $rules['lastTravelHour']; // 0
        $firstTravelHour = $rules['firstTravelHour']; // 6
        $hour = $this->end()->hour;
        if ($this->isEndNight($rules) === false) { return $this->end(); }
        if (($hour >= 0) && ($hour <= $firstTravelHour)) {
            return $this->end();
        } else { return $this->end()->copy()->addDays(1); }
    }

    function isDateNight($date, $rules) {
        $lastTravelHour = $rules['lastTravelHour']; // 0
        $firstTravelHour = $rules['firstTravelHour']; // 6
        $hour = $date->hour;

        if ($lastTravelHour > $firstTravelHour) { // 23 - 6
            return (($hour < $firstTravelHour) || ($hour >= $lastTravelHour));
        }
        if ($lastTravelHour < $firstTravelHour) { // 0 - 6
            return (($hour >= $lastTravelHour) && ($hour < $firstTravelHour));
        }
        if ($lastTravelHour == $firstTravelHour) {
            return false;
        }

    }

    private static function nightDuration($rules) {
        $lastTravelHour = $rules['lastTravelHour']; // 0
        $firstTravelHour = $rules['firstTravelHour']; // 6

        if ($lastTravelHour > $firstTravelHour) { // 23 - 6
            return $firstTravelHour + 24 - $lastTravelHour;
        }
        if ($lastTravelHour < $firstTravelHour) { // 0 - 6
            return $firstTravelHour - $lastTravelHour;
        }
        if ($lastTravelHour == $firstTravelHour) {
            return 0;
        }
    }

    private static function dateMidnight($date) {
        return Carbon::createMidnightDate($date->year, $date->month, $date->day);
    }
}


class HotelStay {

    private $town;
    private $start;
    private $end;
    private $price;

    function town() { return $this->town; }
    function start() { return $this->start; }
    function end() { return $this->end; }
    function nights() { return $this->start->diffInDays($this->end); }
    function price() { return $this->price; }

    function __construct($town, $start, $end, $price) {
        $this->town = $town;
        $this->start = $start;
        $this->end = $end;
        $this->price = $price;
    }

}






class Solver {

    public static function solve($p) {

        $result = self::recursiveNonCycleRouter( $p->tnStartName(), $p->start(), $p, self::intermediateTowns($p->p_towns()) );

        $rws = array();
        foreach ($result as $rw) {

            // Only full route ways
            $d = count($p->p_towns());
            if ($p->tnStart() != $p->tnEnd()) { $d += 1; }
            if (count($rw) < $d) { continue; }

            // Calculating price and putting result to property
            $r = array();
            $r['routes'] = $rw;
            usort($r['routes'], function($a, $b) { return $a->start()->timestamp - $b->start()->timestamp; });
            $rws[] = $r;

        }

        $rws = self::linearHosteler($rws, $p);
        $rws = self::prepareRules($rws, $p);
        $rws = self::sortByRules($rws, $p);

        return $rws;

    }

    private static function intermediateTowns($towns) {
        $response = array();
        foreach ($towns as $town => $t) {
            if (isset($t['mode']) === false) { $response[$town] = $t; }
        }
        return $response;
    }

    private static function countIntermediateTowns($towns) {
        return count(self::intermediateTowns($towns));
    }

    private static function recursiveNonCycleRouter($from, $start, $p, $townsToVisit) {
    
        $rws = array();
    
        $stopRecursion = (count($townsToVisit) == 0);
        $towns = $stopRecursion ? [$p->tnEndName() => $p->p_towns()[$p->tnEndName()]] : $townsToVisit;

        $tn_start; $tn_end;

        if (isset($p->p_towns()[$from]['min'])) {
            $tn_start = $start->copy()->addHours(CarbonInterval::make($p->p_towns()[$from]['min'])->totalHours);
        } else {
            $tn_start = $start->copy();
        }

        if (isset($p->p_towns()[$from]['max'])) {
            $tn_end = $start->copy()->addHours(CarbonInterval::make($p->p_towns()[$from]['max'])->totalHours);
        } else {
            $tn_end = $start->copy()->addHours(12);
        }
        

        foreach ($towns as $tn => $t) {

            $routes = $p->routesOf($from, $tn, $tn_start->copy(), $tn_end->copy());

            $townsToVisitCOPY = $townsToVisit;
            unset($townsToVisitCOPY[$tn]);

            foreach ($routes as $r) {
                $rw = $stopRecursion ? [] : self::recursiveNonCycleRouter($tn, $r->end()->copy(), $p, $townsToVisitCOPY);

                if (count($rw) == 0) { $rws[] = array($r); }
                foreach ($rw as $rww) {
                    $rww[] = $r;
                    $rws[] = $rww;
                }
                
            }

        }
    
        return $rws;
    
    }

    private static function linearHosteler($rws, $p) {

        $rules = $p->p_rules();

        $result = $rws;
        foreach ($result as $III => $rw) {
            $stays = array();
            $parts = $rw['routes'];
            for ($i=0; $i < count($parts); $i++) {
                //echo $i . "\n";
                $r = $parts[$i];
                if ($i != 0) {
                    //echo 'not first' . "\n"; 
                    if ($r->isStartNight($rules)) {
                        //echo 'start at night' . "\n";
                        $stays[] = (new HostelDataProvider($r->fromTnName(), $r->actualStart($rules), $r->actualStart($rules)->copy()->addDays(1)))->searchAccomodation($p);
                    }
                }
                if ($i != count($parts) - 1) {
                    //echo 'not last' . "\n"; 
                    if ($r->isEndNight($rules)) {
                        //echo 'end at night' . "\n";
                        $stays[] = (new HostelDataProvider($r->toTnName(), $r->actualEnd($rules)->copy()->subDays(1), $r->actualEnd($rules)))->searchAccomodation($p);
                    }
                    $r_next = $parts[$i + 1];
                    //echo $r->actualEnd($rules)->toDateTimeString() . " -- " . $r_next->actualStart($rules)->toDateTimeString() . "\n";
                    if ($r->actualEnd($rules)->isSameDay($r_next->actualStart($rules)) === false) {
                        $stays[] = (new HostelDataProvider($r->toTnName(), $r->actualEnd($rules), $r_next->actualStart($rules)))->searchAccomodation($p);
                    }
                }
            }
            //echo $III;
            $result[$III]['stays'] = $stays;
        }

        return $result;

    }

    private static function prepareRules($rws, $p) {
        foreach ($p->p_rules()['advanced'] as $rule => $args) {
            $rws = $rule::prepare($rws, $args);
        }
        return $rws;
    }

    private static function sortByRules($rws, $p) {
        foreach ($p->p_rules()['advanced'] as $rule => $args) {
            return $rule::sort($rws, $args);
        }
        return $rws;
        //return CollisionSorter::sort($rws, $p);
    }

}



?>