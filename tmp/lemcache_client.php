<?php

$memcached = new Memcached();
$memcached->addServer('127.0.0.1', 11211);
// $memcached->set('key', "kanban");

var_dump($memcached->get('key'));      // boolean false
