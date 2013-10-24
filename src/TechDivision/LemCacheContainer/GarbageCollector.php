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
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <pd@techdivision.com>
 */
class GarbageCollector extends \Thread {

    /**
     * StackableArray as Central Storage between threads
     * @var \Stackable
     */
    public $store;

    /**
     * Mutex to Lock Stackable
     * @var Mutex
     */
    public $mutex;

    protected $GCPrefix = "1";

    protected $invalidationArray;


    /**
     * Prefix for saving multiple keys inside one Stackable
     *@var string
     */
    protected $storePrefix = "0-";


    public function __construct($store, $mutex)
    {
        $this->store = $store;
        $this->mutex = $mutex;
        $this->invalidationArray = array();
        $this->storePrefix = "0-";

        \Mutex::lock($this->mutex);
        $this->store[$this->getGCPrefix()] = array();
        \Mutex::unlock($this->mutex);
    }

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


    public function run()
    {
        while (true) {
            $startTime = microtime(true);
            $curTime = time();
            \Mutex::lock($this->mutex);
            //save all values in "Invalidation" SubStore inside our Stackable
            $ar = $this->store["1"];
            //delete all values in our Invalidation SubStore
            $this->store["1"] = array();
            \Mutex::unlock($this->mutex);

            #var_dump($this->invalidationArray);
            $asd = $this->invalidationArray[$curTime];
            if (is_array($asd)) {
                foreach ($asd as $row){
                    \Mutex::lock($this->mutex);
                    unset($this->store[$this->getStorePrefix().$row]);
                    \Mutex::unlock($this->mutex);
                    error_log("key ".$row." deleted...");
                }
            }

            if (is_array($ar)) {
                foreach ($ar as $key=>$value) {
                    if ($value != "0") {
                        $targetTime = $curTime + (int)$value;
                        $tmpar = $this->invalidationArray;
                        if (!$tmpar[$targetTime]) {
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

    protected function getInvalidationArray()
    {
        return $this->invalidationArray;
    }

    protected function calculateDeltaTime($startTime, $finishTime)
    {
        $diffTime = $finishTime - $startTime;
        $deltaTime = (float)1 - $diffTime;
        $deltaTime = floor($deltaTime*1000000);
        $deltaTime = (int)$deltaTime+1;

        return $deltaTime;
    }
}