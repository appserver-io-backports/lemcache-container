<?php

/**
 * TechDivision\LemCacheContainer\GarbageCollector
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\LemCacheContainer;

/**
 * The Thread is responsible for garbageCollection
 *
 * @package TechDivision\LemCacheContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Philipp Dittert <pd@techdivision.com>
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
     * Mutex to Lock Stackable
     *
     * @var Mutex
     */
    public $mutex;

    /**
     * Holds timestamp and key for invalidataion of entrys
     *
     * @var array
     */
    protected $invalidationArray;

    /**
     * Prefix for saving multiple keys inside one Stackable
     *
     * @var string
     */
    protected $storePrefix;

    /**
     * GarbageCollector Prefix for Saving multiple instances in one StackableArray
     *
     * @var string
     */
    protected $GCPrefix;

    /**
     *
     * @param
     *            $store
     * @param
     *            $mutex
     */
    public function __construct($store, $mutex)
    {
        $this->store = $store;
        $this->mutex = $mutex;
        $this->invalidationArray = array();
        $this->storePrefix = "0-";
        $this->GCPrefix = "1";
        
        \Mutex::lock($this->mutex);
        $this->store[$this->getGCPrefix()] = array();
        \Mutex::unlock($this->mutex);
    }

    /**
     * This Method is called wenn thread is started
     *
     * @return void
     */
    public function run()
    {
        while (true) {
            $startTime = microtime(true);
            $curTime = time();
            \Mutex::lock($this->mutex);
            // save all values in "Invalidation" SubStore inside our Stackable
            $ar = $this->store[$this->getGCPrefix()];
            // delete all values in our Invalidation SubStore
            $this->store[$this->getGCPrefix()] = array();
            \Mutex::unlock($this->mutex);
            
            $asd = $this->invalidationArray[$curTime];
            if (is_array($asd)) {
                foreach ($asd as $row) {
                    \Mutex::lock($this->mutex);
                    unset($this->store[$this->getStorePrefix() . $row]);
                    \Mutex::unlock($this->mutex);
                }
            }
            
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
            $finishTime = microtime(true);
            $sleepTime = $this->calculateDeltaTime($startTime, $finishTime);
            usleep($sleepTime);
        }
    }

    /**
     * Calculate difference between these Timestamaps, an substract it from rounded up value of it self
     *
     * @param float $startTime            
     * @param float $finishTime            
     * @return int
     */
    protected function calculateDeltaTime($startTime, $finishTime)
    {
        $diffTime = $finishTime - $startTime;
        $roundedDiffTime = (float) ceil($diffTime);
        // we don't expect a longer runtime than 1 second
        // if we hit this value we return FALSE and our loop will run immediately again
        if ($roundedDiffTime > 1) {
            return FALSE;
        }
        
        $deltaTime = $roundedDiffTime - $diffTime;
        // we need a integer microsecond value (1 million = 1 Second)
        $deltaTime = floor($deltaTime * 1000000);
        // add 1 microsecond (perhaps useful)
        $deltaTime = (int) $deltaTime + 1;
        
        return $deltaTime;
    }

    /**
     * return GarbageCollector Prefix
     *
     * @return string
     */
    protected function getGCPrefix()
    {
        return $this->GCPrefix;
    }

    /**
     * get string $storePrefix
     *
     * @return string
     */
    protected function getStorePrefix()
    {
        return $this->storePrefix;
    }

    /**
     * return invalidationArray
     *
     * @return array
     */
    protected function getInvalidationArray()
    {
        return $this->invalidationArray;
    }
}