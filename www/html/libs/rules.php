<?php

require_once('libs/autoload.php');
use Carbon\Carbon;
use Carbon\CarbonInterval;

class Rule {

    // const PROPERTY = '';
    static public function prepare($rws, $args) {
        foreach ($rws as $I => $rw) {
            foreach ($rw as $r) {
                
            }
        }
        return $rws;
    }

    static public function sort($rws, $args) {
        usort($rws, function($a, $b) { return 1; });
        return $rws;
    }

}

class Price extends Rule {

    const PROPERTY = 'price';

    static public function prepare($rws, $args) {
        foreach ($rws as $I => $rw) {
            $rws[$I][self::PROPERTY] = 0;
            foreach ($rw['routes'] as $r) {
                $rws[$I][self::PROPERTY] += $r->price();
            }
            foreach ($rw['stays'] as $r) {
                $rws[$I][self::PROPERTY] += $r->price();
            }
        }
        return $rws;
    }

    static public function sort($rws, $args) {
        usort($rws, function($a, $b) { return 10000 * ($a[self::PROPERTY] - $b[self::PROPERTY]); });
        return $rws;
    }

}

class MinTransportDuration extends Rule {

    const PROPERTY = 'transportDuration';

    static public function prepare($rws, $args) {
        foreach ($rws as $I => $rw) {
            $rws[$I][self::PROPERTY] = 0;
            foreach ($rw['routes'] as $r) {
                $rws[$I][self::PROPERTY] += $r->durationInHours();
            }
        }
        return $rws;
    }

    static public function sort($rws, $args) {
        usort($rws, function($a, $b) { return 10000 * ($a[self::PROPERTY] - $b[self::PROPERTY]); });
        return $rws;
    }

}

class LessDayTrips extends Rule {

    const PROPERTY = 'dayTripsHours';
    const PROPERTY2 = 'nightTripsHours';


    static public function prepare($rws, $args) {
        foreach ($rws as $I => $rw) {
            $rws[$I][self::PROPERTY] = 0;
            $rws[$I][self::PROPERTY2] = 0;
            foreach ($rw['routes'] as $r) {
                $rws[$I][self::PROPERTY] += $r->dayDurationInHours($args);
                $rws[$I][self::PROPERTY2] += $r->durationInHours() - $r->dayDurationInHours($args);
            }
        }
        return $rws;
    }

    static public function sort($rws, $args) {
        usort($rws, function($a, $b) { return 10000 * ($a[self::PROPERTY] - $b[self::PROPERTY]); });
        return $rws;
    }

}

class LessNightTrips extends Rule {

    const PROPERTY = 'dayTripsHours';
    const PROPERTY2 = 'nightTripsHours';


    static public function prepare($rws, $args) {
        foreach ($rws as $I => $rw) {
            $rws[$I][self::PROPERTY] = 0;
            $rws[$I][self::PROPERTY2] = 0;
            foreach ($rw['routes'] as $r) {
                $rws[$I][self::PROPERTY] += $r->dayDurationInHours($args);
                $rws[$I][self::PROPERTY2] += $r->durationInHours() - $r->dayDurationInHours($args);
            }
        }
        return $rws;
    }

    static public function sort($rws, $args) {
        usort($rws, function($a, $b) { return 10000 * ($a[self::PROPERTY2] - $b[self::PROPERTY2]); });
        return $rws;
    }

}




class CollisionSorter {

    const PROPERTY = '_rank';

    static public function sort($rws, $p) {
        return LessDayTrips::sort($rws, NULL);
        foreach ($rws as $I => $rw) {
            $rws[$I][self::PROPERTY] = 0;
            $rws[$I][self::PROPERTY] = $rws[$I]['transportDuration'];
            /*
            foreach ($rw['routes'] as $r) {
                $rws[$I][self::PROPERTY] += $r->durationInHours();
            }
            */
        }
        usort($rws, function($a, $b) { return 10000000 * ($a[self::PROPERTY] - $b[self::PROPERTY]); });
        return $rws;
    }

}

?>