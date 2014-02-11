<?php
/**
 * TechDivision\LemCacheContainer\MemCacheEntryTest
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    TechDivision
 * @subpackage LemCacheContainer
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

namespace TechDivision\LemCacheContainer\Api;

use TechDivision\LemCacheContainer\Api\MemCacheEntry;

/**
 *
 * <REPLACE WITH CLASS DESCRIPTION>
 *
 * @category   AppServer
 * @package    TechDivision
 * @subpackage LemCacheContainer
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 * @covers     MemCacheEntry
 */
class MemCacheEntryTest extends \PHPUnit_Framework_TestCase
{
    const REQUEST_KEY_ACTION = 0;
    const REQUEST_KEY_KEY = 1;
    const REQUEST_KEY_COMPRESSION_ALLOWED_FLAG = 2;
    const REQUEST_KEY_EXPIRETIME_IN_SECONDS = 3;
    const REQUEST_KEY_DATA_LENGTH_IN_BYTES = 4;
    const REQUEST_KEY_DATA = 5;

    //Member
    public $memCacheEntry;

    //Testsituation Data
    public $crappyRequest;
    public $setRequest;

    public function setUp()
    {
        //Init environment
        $this->memCacheEntry = new MemCacheEntry();

        //Init testdata
        $this->crappyRequest = "ABCDEFG";
        $this->setRequest = $this->buildSetRequestAsLine();
    }


    /**
     * @covers MemCacheEntry::getData
     */

    public function testGetData()
    {
        //Shit in: Shit out
        $this->memCacheEntry->push($this->setRequest);
    }

    private function buildSetRequestAsArray()
    {
        $setRequest = array();

        // Requested Action - Set in this case
        $setRequest[self::REQUEST_KEY_ACTION] = "set \r\n";
        // Key to set the Data
        $setRequest[self::REQUEST_KEY_KEY] = "Key";
        // Compression not allowed here
        $setRequest[self::REQUEST_KEY_COMPRESSION_ALLOWED_FLAG] = 0;
        // Testdata lasts 60 Seconds
        $setRequest[self::REQUEST_KEY_EXPIRETIME_IN_SECONDS] = 60;
        // Testdata has length 4
        $setRequest[self::REQUEST_KEY_DATA_LENGTH_IN_BYTES] = 4;
        // Testdata itselfs
        $setRequest[self::REQUEST_KEY_DATA] = "Test";

        return $setRequest;
    }

    private function buildSetRequestAsLine()
    {
        $setRequest = $this->buildSetRequestAsArray();
        $setRequestAsLine = "";

        foreach($setRequest as $partialRequest){
            $setRequestAsLine .= $partialRequest;
            $setRequestAsLine .= " ";
        }

        return $setRequestAsLine;
    }
}

