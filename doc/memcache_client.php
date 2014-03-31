<?php

/**
 * TechDivision\LemCacheContainer\Api\MemCacheTest
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    TechDivision\LemCacheContainer
 * @subpackage Api
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

$Memcached = new Memcached();
$Memcached->addServer('localhost', 11211);
$Memcached->set('key', "kanban");
var_dump($Memcached->get('key'));      // boolean false
