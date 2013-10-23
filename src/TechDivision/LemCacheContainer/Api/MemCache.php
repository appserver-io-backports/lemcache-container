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
        $this->store = $store;
        $this->store[0] = array();
        $this->mutex = $mutex;
    }

    /**
     * central Method for pushing data into api-object
     *
     * @param string $request
     * @return void
     */
    public function push($request)
    {
        // check if the intial connecten is already initiated and only data are expected
        // else parse this request and select fitting action
        if ($this->isAction()) {
            $this->pushData($request);
        } else {
            if (($var = $this->parseRequest($request)) !== FALSE) {
                switch ($var[0]) {
                    case "set":
                        $this->setAction($var);
                        break;
                    case "get":
                        $this->GetAction($var);
                        break;
                    case "delete":
                        $this->DeleteAction($var);
                        break;
                    case "quit":
                        $this->QuitAction($var);
                        break;
                    default:
                        $this->setState("reset");
                        $this->setResponse("ERROR");
                        break;
                }
                unset($var);
            } else {
                
                $this->setState("reset");
                $this->setResponse("ERROR");
            }
        }
    }

    /**
     * Parse Request
     *
     * @param $request
     * @return array|bool
     */
    protected function parseRequest($request)
    {
        // emtpy request or only a NewLine is not allowed
        if (!$request OR $request == "\n" OR $request == "\r\n") {
            return FALSE;
        }

        // strip header from request (in case of a set request e.g)
        $header = strstr($request, $this->getNewLine(), TRUE);

        $data = substr(strstr($request, $this->getNewLine()),strlen($this->getNewLine()));
        // try to read action
        $var = explode(" ", trim($header));
        //append rest of this request in "data" key
        $var['data'] = $data;

        return $var;
    }

    /**
     * Memcache "set" Action
     *
     * @param array $request
     * @return void
     */
    protected function SetAction($request)
    {
        $this->setIsAction(TRUE);
        try {
                // set Action to "set"
                $this->key = $request[1];

                // validate Flag Value
                if (is_numeric($request[2])) {
                    $this->setFlags($request[2]);
                } else {
                    throw new \Exception("CLIENT_ERROR bad command line format");
                }

                // validate Expiretime value
                if (is_numeric($request[3])) {
                    $this->setExpTime($request[3]);
                } else {
                    throw new \Exception("CLIENT_ERROR bad data chunk");
                }

                // validate data-length in bytes
                if (is_numeric($request[4])) {
                    $this->setBytes($request[4]);
                } else {
                    throw new \Exception("CLIENT_ERROR bad data chunk");
                }

                $this->setState("resume");
                $this->setResponse("");

                if ($request['data']) {

                    $this->pushData($request['data']);
                }

        } catch (\Exception $e) {
            $this->setState("resume");
            $this->setResponse($e->getMessage());
        }
    }

    /**
     * Memcache "get" Action
     *
     * @param array $request
     * @return bool|void
     */
    protected function GetAction($request)
    {
        $key = $request[1];
        // read response from Store
        $response = $this->StoreGet($key);
        // api object should deleted after sending response to client
        $this->setState("reset");
        // set Response for client communication
        $this->setResponse($response);
    }

    /**
     * MemCache "delete" Action
     *
     * @param array $request
     * @return void
     */
    protected function DeleteAction($request)
    {
        $key = $request[1];
        // read response from Store
        $response = $this->StoreDelete($key);
        // api object should deleted after sending response to client
        $this->setState("reset");
        // set Response for client communication
        $this->setResponse($response);
    }

    /**
     * Memcache "quit" Action
     *
     * @param array $request
     * @return void
     */
    protected function QuitAction($request)
    {
        // api object should deleted after sending response to client
        $this->setState("close");

        // set Response for client communication
        $this->setResponse("");
    }

    /**
     * Method for validating "value" Data for "set" and "add" Action.
     * Check if Bytes value is reached and set State/Response
     *
     * @param $data
     * @return void
     */
    protected function pushData($data)
    {
        if ($data == $this->getNewline() && strlen($this->getData()) == $this->getBytes()) {
            $this->StoreSet($this->getKey(), $this->getFlags(), $this->getExpTime(), $this->getBytes(), $this->getData());
            $this->setState("reset");
            $this->setResponse("STORED");
        } else {
            if ($data != $this->getNewLine()) {$data = rtrim($data);}
            $this->setData($data);
            if (strlen($this->getData()) == $this->getBytes()) {
                $this->StoreSet($this->getKey(), $this->getFlags(), $this->getExpTime(), $this->getBytes(), $this->getData());
                $this->setState("reset");
                $this->setResponse("STORED");
            } elseif (strlen($this->getData()) > $this->getBytes()) {
                $this->setState("reset");
                $this->setResponse("CLIENT_ERROR bad data chunk{$this->getNewLine()}ERROR");
            }
        }
    }
}