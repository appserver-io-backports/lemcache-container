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

    protected $invalidationArray = array();


    /**
     * Prefix for saving multiple keys inside one Stackable
     *@var string
     */
    protected $storePrefix = "0-";


    public function __construct($store, $mutex)
    {
        $this->store = $store;
        $this->mutex = $mutex;

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
            $startTime = microtime();
            $curTime = time();
            #echo "Currenttime: ".$curTime."\n";
            \Mutex::lock($this->mutex);
            //hack
            $ar = $this->store["1"];
            var_dump($ar);
            $this->store["1"] = array();
            \Mutex::unlock($this->mutex);
            $asd = $this->getInvalidationArray()[$curTime];
            if (is_array($asd)) {
                foreach ($asd as $row){
                    \Mutex::lock($this->mutex);
                    unset($this->store[$this->getStorePrefix().$row]);
                    \Mutex::unlock($this->mutex);
                    var_dump("loesche key: ".$row );
                }

            }

            if (is_array($ar)) {
                foreach ($ar as $key=>$value) {
                    $targetTime = $curTime + (int)$value;
                    #echo "TargetTime: ".$targetTime."\n";
                    $this->getInvalidationArray()[$targetTime][] = $key;
                }
            }





            sleep(1);
            $endTime = microtime();
        }
    }

    protected function getInvalidationArray()
    {
        return $this->invalidationArray;
    }
}