<?php

class Cache {

    private $url;
    private $cache;

    function __construct($url) {
        $this->url = $url;
        $this->cache = json_decode(file_get_contents($url), true);
    }

    public function W($method, $args, $data) {
        $this->cache[$method][$args] = $data;
        $this->S();
        //$this->S();
    }

    public function R($method, $args) {
        if (isset($this->cache[$method][$args])) { return $this->cache[$method][$args]; } else { return false; }
    }

    public function S() {
        file_put_contents($this->url, json_encode($this->cache));
    }

    function __destruct() {
        $this->S();
    }

}

global $cache;

// flush cache
//file_put_contents(realpath(__DIR__ . '/..') . '/cache/web.cache', json_encode([]));// die('Cache created.');

$cache = new Cache(realpath(__DIR__ . '/..') . '/cache/web.cache');






















?>