<?php
// Initialize values: 10000 keys of 20 bytes with 40 bytes of data
$c = 10000;
$values = array();
for ($i = 0; $i < $c; $i ++)
    $values[sprintf('%020s', $i)] = sha1($i);
echo "memcache vs memcached: $c keys\n";
// Memcached
$m = new Memcache();
$m->addServer('localhost', 11211);
$start = microtime(true);
foreach ($values as $k => $v)
    $m->set($k, $v, 3600);
$time = microtime(true) - $start;
echo "memcached set: $time\n";
$start = microtime(true);
foreach ($values as $k => $v)
    $m->get($k);
$time = microtime(true) - $start;
echo "memcached get: $time\n";
// Memcache
$m = new Memcache();
$m->addServer('localhost', 11210);
$start = microtime(true);
foreach ($values as $k => $v)
    $m->set($k, $v, 0, 3600);
$time = microtime(true) - $start;
echo "memcache set: $time\n";
$start = microtime(true);
foreach ($values as $k => $v)
    $m->get($k);
$time = microtime(true) - $start;
echo "memcache get: $time\n";
