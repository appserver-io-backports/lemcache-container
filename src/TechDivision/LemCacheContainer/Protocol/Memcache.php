<?php

/**
 * TechDivision\LemCacheContainer\Api\MemCache
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

namespace TechDivision\LemCacheContainer\Api;

use TechDivision\Socket\Client;
use TechDivision\LemCacheContainer\Api\AbstractMemCache;

/**
 * Memcache compatible cache implementation.
 * 
 * @category   Appserver
 * @package    TechDivision_WebSocketContainer
 * @subpackage Api
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class MemCache extends AbstractMemCache
{
    
    /**
     * Stackable array for sharing data between threads.
     *
     * @var \Stackable
     */
    public $store;

    /**
     * Mutex for keeping Data inside store valid.
     *
     * @var integer
     */
    public $mutex;
    
    /**
     * The value object storing the incoming data to be cached.
     * 
     * @var \TechDivision\LemCacheContainer\Api\MemCacheEntry
     */
    public $vo;

    /**
     * Initializes the instance with the store and the mutex value.
     * 
     * @param \Stackable $store The store instance
     * @param integer    $mutex The mutex value
     * 
     * @return void
     */
    public function __construct($store, $mutex)
    {
        $this->reset();
        $this->store = $store;
        $this->store[0] = array();
        $this->mutex = $mutex;
    }

    /**
     * Handle the the passed request VO.
     *
     * @param \TechDivision\LemCacheContaine\Api\MemcacheEntry $vo The VO with the data to handle
     * 
     * @return void
     */
    public function request($vo)
    {
        
        // initialize the VO
        $this->vo = $vo;
        
        //build Methodname from RequestAction und "Action"
        $method = $vo->getRequestAction() . "Action";
        $this->$method();
    }

    /**
     * Memcache "get" action implementation.
     *
     * @return void
     * @todo API object should deleted after sending response to client
     */
    protected function getAction()
    {
        // read response from Store
        $response = $this->storeGet($this->getVO()->getKey());
        
        // set Response for client communication
        $this->setResponse($response);
    }

    /**
     * Memcache "set" Action
     *
     * @return void
     */
    protected function setAction()
    {
        $vo = $this->getVO();
        
        if ($this->storeSet($vo->getKey(), $vo->getFlags(), $vo->getExpTime(), $vo->getBytes(), $vo->getData())) {
            $this->setResponse("STORED");
            return;
        }
            
        $this->setResponse("NOT_STORED");
        
        // api object should deleted after sending response to client
        $this->setState("close");
    }

    /**
     * Memcache "add" Action
     *
     * @return void
     */
    protected function addAction()
    {
        $vo = $this->getVO();

        if (!$this->storeKeyExists($vo->getKey())) {
            if ($this->storeAdd($vo->getKey(), $vo->getFlags(), $vo->getExpTime(), $vo->getBytes(), $vo->getData())) {
                $this->setResponse("STORED");
                return;
            }
        }
            
        $this->setResponse("NOT_STORED");
    }

    /**
     * Memcache "replace" Action
     *
     * @return void
     */
    protected function replaceAction()
    {
        $vo = $this->getVO();

        if ($this->storeKeyExists($vo->getKey())) {
            if ($this->storeSet($vo->getKey(), $vo->getFlags(), $vo->getExpTime(), $vo->getBytes(), $vo->getData())) {
                $this->setResponse("STORED");
                return;
            }
        }
        
        $this->setResponse("NOT_STORED");
    }

    /**
     * Memcache "append" Action
     *
     * @return void
     */
    protected function appendAction()
    {
        $vo = $this->getVO();

        //check if Key exits
        if ($this->storeKeyExists($vo->getKey())) {
            //read Entry in Raw (array) Format for faster processing
            $ar = $this->storeRawGet($vo->getKey());
            //append new Data
            $ar['data'] .= $vo->getData();
            //save extends Entry to Store
            $this->storeRawSet($ar);

            $this->setResponse("STORED");
        } else {
            $this->setResponse("NOT_STORED");
        }
    }

    /**
     * Memcache "prepend" Action
     *
     * @return void
     */
    protected function prependAction()
    {
        $vo = $this->getVO();

        //check if Key exits
        if ($this->storeKeyExists($vo->getKey())) {
            //read Entry in Raw (array) Format for faster processing
            $ar = $this->storeRawGet($vo->getKey());
            //append new Data
            $ar['data'] = $vo->getData().$ar['data'];
            //save extends Entry to Store
            $this->storeRawSet($ar);

            $this->setResponse("STORED");
        } else {
            $this->setResponse("NOT_STORED");
        }
    }

    /**
     * Memcache "touch" Action
     *
     * @return void
     */
    protected function touchAction()
    {
        $vo = $this->getVO();

        //check if Key exits
        if ($this->storeKeyExists($vo->getKey())) {
            //read Entry in Raw (array) Format for faster processing
            $ar = $this->storeGet($vo->getKey());
            //append new Data
            $ar['expTime'] = $vo->getExpTime();
            //save extends Entry to Store
            $this->storeRawSet($ar);

            $this->setResponse("TOUCHED");
        } else {
            $this->setResponse("NOT_FOUND");
        }
    }



    /**
     * MemCache "delete" Action
     *
     * @return void
     */
    protected function deleteAction()
    {
        // read response from Store
        $response = $this->storeDelete($this->getVO()->getKey());
        // api object should deleted after sending response to client
        $this->setState("reset");
        // set Response for client communication
        $this->setResponse($response);
    }

    /**
     * Memcache "quit" Action
     *
     * @return void
     */
    protected function quitAction()
    {
        // api object should deleted after sending response to client
        $this->setState("close");
        
        // set Response for client communication
        $this->setResponse("");
    }

    /**
     * Memcache "increment" Action
     *
     * @return void
     */
    protected function incrementAction()
    {
        
        // read response from Store
        $response = $this->storeIncrement($this->getVO()->getKey(), $this->getVO()->getData());
        // set Response for client communication
        $this->setResponse($response);
    }

    /**
     * Memcache "decrement" Action
     *
     * @return void
     */
    protected function decrementAction()
    {
        // read response from Store
        $response = $this->storeDecrement($this->getVO()->getKey(), $this->getVO()->getData());
        // set Response for client communication
        $this->setResponse($response);
    }
}
