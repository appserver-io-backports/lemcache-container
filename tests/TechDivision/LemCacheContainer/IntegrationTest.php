<?php

/**
 * TechDivision\LemCacheContainer\ConfigurationTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_LemCacheContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\LemCacheContainer;

/**
 * Integration test class to run tests against installed appserver.io LemCache instance.
 *
 * @category  Appserver
 * @package   TechDivision_LemCacheContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
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

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('PECL memcache extension not loaded');
        }

        // initialize the memcache client to run the tests with
        $this->memcache = new \Memcache();

        // initialize the configuration variables
        $server['host'] = IntegrationTest::DEFAULT_HOST;
        $server['port'] = IntegrationTest::DEFAULT_PORT;
        $server['persistent'] = IntegrationTest::DEFAULT_PERSISTENT;
        $server['weight'] = IntegrationTest::DEFAULT_WEIGHT;
        $server['timeout'] = IntegrationTest::DEFAULT_TIMEOUT;
        $server['retry_interval'] = IntegrationTest::DEFAULT_RETRY_INTERVAL;
        $server['status'] = IntegrationTest::DEFAULT_STATUS;
        $server['failure_callback'] = IntegrationTest::DEFAULT_FAILURE_CALLBACK;

        // add the server configuration
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
     * Test getter/setter with small data piece.
     *
     * @return void
     */
    public function testSetAndGet()
    {

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('PECL memcache extension not loaded');
        }

        // initialize the variables to get/set
        $lifetime = $this->getLifetime();
        $id = 'key';
        $time = time();
        $data = 'Some data to be set';
        $flag = 0; // we want NO compression here

        // ZF-8856: using set because add needs a second request if item already exists
        $result = $this->memcache->set($id, array($data, $time, $lifetime), $flag, $lifetime);

        // check that the data has been added
        $this->assertTrue($result);
        $this->assertEquals(array($data, $time, $lifetime), $this->memcache->get($id));
    }

    /**
     * Test getter/setter with small data piece and a line break.
     *
     * @return void
     */
    public function testSetAndGetWithLineBreak()
    {

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('PECL memcache extension not loaded');
        }

        // initialize the variables to get/set
        $lifetime = $this->getLifetime();
        $id = 'key';
        $time = time();
        $data = 'Some data
            to be set';
        $flag = 0; // we want NO compression here

        // ZF-8856: using set because add needs a second request if item already exists
        $result = $this->memcache->set($id, array($data, $time, $lifetime), $flag, $lifetime);

        // check that the data has been added
        $this->assertTrue($result);
        $this->assertEquals(array($data, $time, $lifetime), $this->memcache->get($id));
    }

    /**
     * Test getter/setter with a big data piece.
     *
     * @return void
     */
    public function testSetAndGetBigData()
    {

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('PECL memcache extension not loaded');
        }

        // initialize the variables to get/set
        $lifetime = $this->getLifetime();
        $id = 'bigDataKey';
        $time = time();
        $data = file_get_contents(__DIR__ . '/_files/big_data.txt');
        $flag = 0; // we want NO compression here

        // ZF-8856: using set because add needs a second request if item already exists
        $result = $this->memcache->set($id, array($data, $time, $lifetime), $flag, $lifetime);

        // check that the data has been added
        $this->assertTrue($result);
        $this->assertEquals(array($data, $time, $lifetime), $this->memcache->get($id));
    }

    /**
     * Test getter/setter with a big data piece.
     *
     * Example data from Magento:
     *
     * array (
     *     0 => 'incr',
     *     1 => 'oi2juh0qnh3u8lf5d1ffj5h5n0.lock',
     *     2 => '1',
     *     'data' => 'add oi2juh0qnh3u8lf5d1ffj5h5n0.lock 768 15 1
     * 1
     * get oi2juh0qnh3u8lf5d1ffj5h5n0
     * '
     * )
     *
     * @return void
     */
    public function testIncrementWithValue()
    {

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('PECL memcache extension not loaded');
        }

        // initialize the variables to get/set
        $lifetime = $this->getLifetime();
        $id = 'oi2juh0qnh3u8lf5d1ffj5h5n0.lock';
        $data = 1;
        $flag = 0; // we want NO compression here

        // ZF-8856: using set because add needs a second request if item already exists
        $this->memcache->set($id, $data, $flag, $lifetime);
        $result = $this->memcache->increment($id, $newValue = 555);

        // check that the data has been added
        $this->assertEquals($newValue, $result);
        $this->assertEquals($newValue, $this->memcache->get($id));
    }

    /**
     * Test if the cache will successfully be flushed.
     *
     * @return void
     */
    public function testFlush()
    {

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('PECL memcache extension not loaded');
        }

        // initialize the variables to get/set
        $lifetime = $this->getLifetime();
        $id = 'flushKey';
        $time = time();
        $data = 'Some data to be set';
        $flag = MEMCACHE_COMPRESSED; // we WANT compression here

        // ZF-8856: using set because add needs a second request if item already exists
        $result = $this->memcache->set($id, array($data, $time, $lifetime), $flag, $lifetime);
        $this->assertTrue($result);

        // flush the cache and check that the previously added data is NOT available anymore
        $this->memcache->flush();
        $this->assertFalse($this->memcache->get($id));
    }
}
