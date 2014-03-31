<?php


$Memcached = new Memcached();
$Memcached->addServer('localhost', 11211);
$Memcached->set('key', "kanban");
var_dump($Memcached->get('key'));      // boolean false
?>
