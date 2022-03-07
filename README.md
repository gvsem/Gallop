
## Gallop API

An API to build complex routes across the Europe using heuristics and stuff.

### Features

* Time spans for cities
* Heuristics to define optimal solution
* BusRadar.com & Hostelbookers.com providers support

### Example
```php
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
```

**Start date:** 2nd August 2018.

**Submit date:** 28st August 2018.

**Title:** Gallop Travelling API Solution

**Mission statement:** Journey planning must be more simple for tourist. Digital solutions should be used to provide relevant and stoic ways of travelling. My API is created to start realising this problem.


