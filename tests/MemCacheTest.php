<?php
/**
 * TechDivision\LemCacheContainer\Api\MemCacheTest
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    TechDivision\LemCacheContainer
 * @subpackage Api
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

namespace TechDivision\LemCacheContainer\Api;

/**
 *
 * <REPLACE WITH CLASS DESCRIPTION>
 *
 * @category   AppServer
 * @package    TechDivision\LemCacheContainer
 * @subpackage Api
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 *
 */
class MemCacheTest extends \PHPUnit_Framework_TestCase
{
    const LOCALHOST = 'localhost';

    /*
     * There is the possibility to switch between the memcached or the
     * aphpserver lemcache implementation to test
     */
    const LEMCACHE_PORT = 11210;
    const MEMCACHE_PORT = 11211;

    public $memcached;
    public $key;
    public $expectedData;
    public $expectedExpireTime;

    public function setUp()
    {
        // Init a client for testing the lemcache implementation
        $this->memcached = new \Memcached();
        $this->memcached->addServer(self::LOCALHOST, self::MEMCACHE_PORT);

        // Default setting: compression enabled
        $this->memcached->setOption(\Memcached::OPT_COMPRESSION, false);

        // Invalidates all entries inside the store to ensure that testcase independency is given
        $this->memcached->flush();

        // Init testdata
        $this->key = 'key';
        $this->expectedData = "testData";
        $this->expectedExpireTime = 5; //Seconds
    }

    /**
     * MemCache "set" Action Test
     */
    public function testSetAndGetAction()
    {
        // Set data to specified key
        $this->memcached->set($this->key, $this->expectedData);

        // Get data from store using the specified key
        $result = $this->memcached->get($this->key);

        $this->assertEquals($this->expectedData, $result);
    }

    /**
     * MemCache "add" Action Test
     */
    public function testAddAction()
    {
        $secondKey = 'key2';

        // Set data to specified key
        $this->memcached->set($this->key, $this->expectedData);

        // Try to add item under the same key again
        $this->assertFalse($this->memcached->add($this->key, $this->expectedData));
        // Try to add item under new key
        $this->assertTrue($this->memcached->add($secondKey, $this->expectedData));

        // Get data from store using the new assigned key
        $result = $this->memcached->get($secondKey);

        $this->assertEquals($this->expectedData, $result);
    }

    /**
     * MemCache "replace" Action Test
     */
    public function testReplaceAction()
    {
        $replacingData = "replacingTestData";

        // Fails if the key is not registered in the store yet
        $this->assertFalse($this->memcached->replace($this->key, $this->expectedData));

        // Sets the required key value pair
        $this->memcached->set($this->key, $this->expectedData);

        // Succeeds because of the existing key
        $this->assertTrue($this->memcached->replace($this->key, $replacingData));
        $result = $this->memcached->get($this->key);

        $this->assertEquals($replacingData, $result);
    }

    /**
     * MemCache "append" Action Test
     *
     * (requires string data and deaktivated compression)
     */
    public function testAppendAction()
    {
        $appendedData = "appendedTestData";

        // Appending only works with disabled compression
        $this->memcached->setOption(\Memcached::OPT_COMPRESSION, false);

        // Sets data to append data to
        $this->memcached->set($this->key, $this->expectedData);
        $this->memcached->append($this->key, $appendedData);

        $result = $this->memcached->get($this->key);
        $expected = $this->expectedData . $appendedData;

        $this->assertEquals($expected, $result);
    }

    /**
     * MemCache "prepend" Action Test
     *
     * (requires string data and deaktivated compression)
     */
    public function testPrependAction()
    {
        $prependedData = "prependedTestData";

        // Prependition only works with disabled compression
        $this->memcached->setOption(\Memcached::OPT_COMPRESSION, false);

        // Sets data to append data to
        $this->memcached->set($this->key, $this->expectedData);
        $this->memcached->prepend($this->key, $prependedData);

        $result = $this->memcached->get($this->key);
        $expected = $prependedData . $this->expectedData;

        $this->assertEquals($expected, $result);
    }

    /**
     * MemCache "touch" Action Test
     */
    public function testTouchAction()
    {
        $newExpirationTime = 30; // Seconds
        $enoughTimeToExecuteTouch = 2; // Seconds

        // Binary Protocol required for this operation
        $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);

        // Set data using an expire time
        $this->memcached->set($this->key, $this->expectedData, $this->expectedExpireTime);

        // Sleep as long the expire time lasts
        sleep($this->expectedExpireTime - $enoughTimeToExecuteTouch);

        // Expand expiretime with touch
        $this->memcached->touch($this->key, $newExpirationTime);
        sleep($enoughTimeToExecuteTouch);
        $this->assertEquals($this->expectedData, $this->memcached->get($this->key));
    }

    /**
     * MemCache "delete" Action Test
     */
    public function testDeleteAction()
    {
        // Unavailable entries cant be deleted
        $this->assertFalse($this->memcached->delete($this->key));
        $this->memcached->set($this->key, $this->expectedData);

        // Return true if entry can be deleted
        $this->assertTrue($this->memcached->delete($this->key));
    }

    /**
     * MemCache "quit" Action Test
     */
    public function testQuitAction()
    {
        // Store data in the memcache instance to check if session was killed correct
        $this->memcached->set($this->key,$this->expectedData);

        // Close all connections to the instance and kills session
        $this->assertTrue($this->memcached->quit());

        // Memcache should rebuild a connection automatically
        $this->assertEquals($this->expectedData,$this->memcached->get($this->key));
    }
}

