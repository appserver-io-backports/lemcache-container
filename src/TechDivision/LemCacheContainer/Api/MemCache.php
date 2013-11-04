<?php

/**
 * TechDivision\LemCacheContainer\Api\MemCache
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */


namespace TechDivision\LemCacheContainer\Api;

use TechDivision\Socket\Client;
use TechDivision\LemCacheContainer\Api\AbstractMemCache;


/**
 * The http client implementation that handles the request like a webserver
 *
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */


class MemCache extends AbstractMemCache
{
    /**
     * StackableArray for sharing data between threads
     *
     * @var array
     */
    public $store;

    /**
     * Mutex for keeping Data inside $store valid
     *
     * @var int
     */
    public $mutex;

    public function __construct($store, $mutex)
    {
        $this->reset();
        $this->store = $store;
        $this->store[0] = array();
        $this->mutex = $mutex;
    }

    /**
     * Get da Request ValueObject and do something
     *
     * @param $vo
     * @return void
     */
    public function request($vo)
    {
        $this->vo = $vo;
        //build Methodname from RequestAction und "Action"
        $method = $vo->getRequestAction()."Action";
        $this->$method();
    }

    /**
     * Memcache "get" Action
     *
     * @return bool|void
     */
    protected function getAction()
    {
        // read response from Store
        $response = $this->StoreGet($this->getVO()->getKey());
        // api object should deleted after sending response to client
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
        $this->StoreSet($vo->getKey(), $vo->getFlags(), $vo->getExpTime(), $vo->getBytes(), $vo->getData());
        $this->setResponse("STORED");
    }

    /**
     * Memcache "add" Action
     *
     * @return void
     */
    protected function addAction()
    {
        $vo = $this->getVO();

        if (!$this->StoreKeyExists($vo->getKey())) {
            $this->StoreSet($vo->getKey(), $vo->getFlags(), $vo->getExpTime(), $vo->getBytes(), $vo->getData());
            $this->setResponse("STORED");
        } else {
            $this->setResponse("NOT_STORED");
        }
    }

    /**
     * Memcache "replace" Action
     *
     * @return void
     */
    protected function replaceAction()
    {
        $vo = $this->getVO();

        if ($this->StoreKeyExists($vo->getKey())) {
            $this->StoreSet($vo->getKey(), $vo->getFlags(), $vo->getExpTime(), $vo->getBytes(), $vo->getData());
            $this->setResponse("STORED");
        } else {
            $this->setResponse("NOT_STORED");
        }
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
        if ($this->StoreKeyExists($vo->getKey())) {
            //read Entry in Raw (array) Format for faster processing
            $ar = $this->StoreRawGet($vo->getKey());
            //append new Data
            $ar['data'] .= $vo->getData();
            //save extends Entry to Store
            $this->StoreRawSet($ar);

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
        if ($this->StoreKeyExists($vo->getKey())) {
            //read Entry in Raw (array) Format for faster processing
            $ar = $this->StoreRawGet($vo->getKey());
            //append new Data
            $ar['data'] = $vo->getData().$ar['data'];
            //save extends Entry to Store
            $this->StoreRawSet($ar);

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
        if ($this->StoreKeyExists($vo->getKey())) {
            //read Entry in Raw (array) Format for faster processing
            $ar = $this->StoreGet($vo->getKey());
            //append new Data
            $ar['expTime'] = $vo->getExpTime();
            //save extends Entry to Store
            $this->StoreRawSet($ar);

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
        $response = $this->StoreDelete($this->getVO()->getKey());
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
}