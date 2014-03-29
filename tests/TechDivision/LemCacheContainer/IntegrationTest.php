<?php

/**
 * TechDivision\LemCacheContainer\ConfigurationTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\LemCacheContainer;

/**
 *
 * @package TechDivision\LemCacheContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Default Values
     */
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11210;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_WEIGHT  = 1;
    const DEFAULT_TIMEOUT = 2;
    const DEFAULT_RETRY_INTERVAL = 15;
    const DEFAULT_STATUS = true;
    const DEFAULT_FAILURE_CALLBACK = null;
    
    /**
     * Available options
     *
     * =====> (array) servers :
     * an array of memcached server ; each memcached server is described by an associative array :
     * 'host' => (string) : the name of the memcached server
     * 'port' => (int) : the port of the memcached server
     * 'persistent' => (bool) : use or not persistent connections to this memcached server
     * 'weight' => (int) : number of buckets to create for this server which in turn control its
     *                     probability of it being selected. The probability is relative to the total
     *                     weight of all servers.
     * 'timeout' => (int) : value in seconds which will be used for connecting to the daemon. Think twice
     *                      before changing the default value of 1 second - you can lose all the
     *                      advantages of caching if your connection is too slow.
     * 'retry_interval' => (int) : controls how often a failed server will be retried, the default value
     *                             is 15 seconds. Setting this parameter to -1 disables automatic retry.
     * 'status' => (bool) : controls if the server should be flagged as online.
     * 'failure_callback' => (callback) : Allows the user to specify a callback function to run upon
     *                                    encountering an error. The callback is run before failover
     *                                    is attempted. The function takes two parameters, the hostname
     *                                    and port of the failed server.
     *
     * =====> (boolean) compression :
     * true if you want to use on-the-fly compression
     *
     * =====> (boolean) compatibility :
     * true if you use old memcache server or extension
     *
     * @var array available options
     */
    protected $options = array(
        'servers' => array(
            array(
                'host' => IntegrationTest::DEFAULT_HOST,
                'port' => IntegrationTest::DEFAULT_PORT,
                'persistent' => IntegrationTest::DEFAULT_PERSISTENT,
                'weight'  => IntegrationTest::DEFAULT_WEIGHT,
                'timeout' => IntegrationTest::DEFAULT_TIMEOUT,
                'retry_interval' => IntegrationTest::DEFAULT_RETRY_INTERVAL,
                'status' => IntegrationTest::DEFAULT_STATUS,
                'failure_callback' => IntegrationTest::DEFAULT_FAILURE_CALLBACK
            )
        ),
        'compression' => false,
        'compatibility' => false,
    );
    
    /**
     * Frontend or Core directives
     *
     * =====> (int) lifetime :
     * - Cache lifetime (in seconds)
     * - If null, the cache is valid forever
     *
     * =====> (int) logging :
     * - if set to true, a logging is activated throw Zend_Log
     *
     * @var array directives
     */
    protected $directives = array(
        'lifetime' => 3600,
        'logging'  => false,
        'logger'   => null
    );

    /**
     * The memcache client instance used for tests.
     *
     * @var \Memcache
     */
    protected $memcache;

    /**
     * Initializes the configuration instance to test.
     *
     * @return void
     */
    public function setUp()
    {
    
        $this->memcache = new \Memcache();
        
        $server['host'] = IntegrationTest::DEFAULT_HOST;
        $server['port'] = IntegrationTest::DEFAULT_PORT;
        $server['persistent'] = IntegrationTest::DEFAULT_PERSISTENT;
        $server['weight'] = IntegrationTest::DEFAULT_WEIGHT;
        $server['timeout'] = IntegrationTest::DEFAULT_TIMEOUT;
        $server['retry_interval'] = IntegrationTest::DEFAULT_RETRY_INTERVAL;
        $server['status'] = IntegrationTest::DEFAULT_STATUS;
        $server['failure_callback'] = IntegrationTest::DEFAULT_FAILURE_CALLBACK;
        
        $this->memcache->addServer(
            $server['host'], 
            $server['port'], 
            $server['persistent'],
            $server['weight'], 
            $server['timeout'],
            $server['retry_interval'],
            $server['status'], 
            $server['failure_callback']
        );
    }

    /**
     * Get the life time
     *
     * if $specificLifetime is not false, the given specific life time is used
     * else, the global lifetime is used
     *
     * @param  int $specificLifetime
     * @return int Cache life time
     */
    public function getLifetime($specificLifetime = false)
    {
        if ($specificLifetime === false) {
            return $this->directives['lifetime'];
        }
        return $specificLifetime;
    }

    /**
     * 
     */
    public function testSetAndGet()
    {
        
        $lifetime = $this->getLifetime();
        $id = 'key';
        $time = time();
        $data = 'Some data to be set';
        $flag = 0; // we want NO compression here

        // ZF-8856: using set because add needs a second request if item already exists
        $result = $this->memcache->set($id, array($data, $time, $lifetime), $flag, $lifetime);

        $this->assertTrue($result);
        $this->assertEquals(array($data, $time, $lifetime), $this->memcache->get($id));
    }
}
