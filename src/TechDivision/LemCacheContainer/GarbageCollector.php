<?php

/**
 * TechDivision\LemCacheContainer\GarbageCollector
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
 * @author    Philipp Dittert <pd@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
namespace TechDivision\LemCacheContainer;

/**
 * This thread is responsible for handling the garbage collection.
 *
 * @category  Appserver
 * @package   TechDivision_WebSocketContainer
 * @author    Philipp Dittert <pd@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class GarbageCollector extends \Thread
{

    /**
     * StackableArray as Central Storage between threads
     *
     * @var \Stackable
     */
    public $store;

    /**
     * Mutex to lock the stackable.
     *
     * @var integer
     */
    public $mutex;

    /**
     * Holds timestamp and key for invalidation of entrys.
     *
     * @var array
     */
    protected $invalidationArray;

    /**
     * Prefix for saving multiple keys inside one stackable.
     *
     * @var string
     */
    protected $storePrefix;

    /**
     * Garbage collector prefix for saving multiple instances in one stackable array.
     *
     * @var string
     */
    protected $GCPrefix;

    /**
     * Initializes the instance with the store and the mutex.
     * 
     * @param \Stackable $store The stackable to store the data with
     * @param integer    $mutex The mutex instance
     * 
     * @return void
     */
    public function __construct($store, $mutex)
    {
        // initialize the member variables
        $this->store = $store;
        $this->mutex = $mutex;
        $this->invalidationArray = array();
        $this->storePrefix = "0-";
        $this->GCPrefix = "1";
        
        // initialize the stackable
        \Mutex::lock($this->mutex);
        $this->store[$this->getGCPrefix()] = array();
        \Mutex::unlock($this->mutex);
    }

    /**
     * This Method is called wenn thread is started.
     *
     * @return void
     */
    public function run()
    {
        
        // start the loop and handle requests
        while (true) {
            
            // initialize the method variables
            $startTime = microtime(true);
            $curTime = time();
            
            \Mutex::lock($this->mutex);
            // save all values in "Invalidation" SubStore inside our Stackable
            $ar = $this->store[$this->getGCPrefix()];
            // delete all values in our Invalidation SubStore
            $this->store[$this->getGCPrefix()] = array();
            \Mutex::unlock($this->mutex);
            
            // prepare the array with the invalid cache entries for the actual timestamp
            $asd = $this->invalidationArray[$curTime];
            
            // if an array with invalid entries has been found, invalidate them
            if (is_array($asd)) {
                foreach ($asd as $row) {
                    \Mutex::lock($this->mutex);
                    unset($this->store[$this->getStorePrefix() . $row]);
                    \Mutex::unlock($this->mutex);
                }
            }
            
            // load the array with the values to be garbage collected
            if (is_array($ar)) {
                foreach ($ar as $key => $value) {
                    if ($value != "0") {
                        $targetTime = $curTime + (int) $value;
                        $tmpar = $this->invalidationArray;
                        if (! $tmpar[$targetTime]) {
                            $tmpar[$targetTime] = array();
                        }
                        $tmpar[$targetTime][] = $key;
                        $this->invalidationArray = $tmpar;
                    }
                }
            }
            
            // clear everything up and sleep
            $finishTime = microtime(true);
            $sleepTime = $this->calculateDeltaTime($startTime, $finishTime);
            usleep($sleepTime);
        }
    }

    /**
     * Calculate difference between these Timestamaps, an substract it 
     * from rounded up value of it self.
     *
     * @param float $startTime  The start time            
     * @param float $finishTime The finish time
     * 
     * @return integer The rounded delta 
     */
    protected function calculateDeltaTime($startTime, $finishTime)
    {
        // calculate and round the value first
        $diffTime = $finishTime - $startTime;
        $roundedDiffTime = (float) ceil($diffTime);
        
        // we don't expect a longer runtime than 1 second
        // if we hit this value we return FALSE and our loop will run immediately again
        if ($roundedDiffTime > 1) {
            return false;
        }
        
        $deltaTime = $roundedDiffTime - $diffTime;
        // we need a integer microsecond value (1 million = 1 Second)
        $deltaTime = floor($deltaTime * 1000000);
        // add 1 microsecond (perhaps useful)
        $deltaTime = (int) $deltaTime + 1;
        
        // return the delta
        return $deltaTime;
    }

    /**
     * Return's the garbage collector prefix.
     *
     * @return string The garbage collector prefix
     */
    protected function getGCPrefix()
    {
        return $this->GCPrefix;
    }

    /**
     * Return's the store prefix.
     *
     * @return string The store prefix
     */
    protected function getStorePrefix()
    {
        return $this->storePrefix;
    }

    /**
     * Return the array with cache entries to be invalidated. 
     *
     * @return array The array with the invalid cache entries
     */
    protected function getInvalidationArray()
    {
        return $this->invalidationArray;
    }
}
