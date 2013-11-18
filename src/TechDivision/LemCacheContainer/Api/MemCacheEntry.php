<?php

/**
 * TechDivision\LemCacheContainer\Api\MemCacheEntry
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */


namespace TechDivision\LemCacheContainer\Api;

use TechDivision\LemCacheContainer\Api\AbstractMemCacheEntry;


/**
 *
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */


class MemCacheEntry extends AbstractMemCacheEntry
{

    /**
     * central Method for pushing data into vo-object
     *
     * @param string $request
     * @return void
     */
    public function push($request)
    {
        // check if the intial connecten is already initiated and only data are expected
        // else parse this request and select fitting action
        if ($this->getRequestAction()) {
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
                        throw new \Exception("");
                        break;
                }
                unset($var);
            } else {
                throw new \Exception("");
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
        $this->SetRequestAction('set');
        $this->setKey($request[1]);

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

        if ($request['data']) {
        $this->pushData($request['data']);
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
        $this->setKey($request[1]);
        $this->SetRequestAction('get');
        $this->setComplete(TRUE);
    }

    /**
     * MemCache "delete" Action
     *
     * @param array $request
     * @return void
     */
    protected function DeleteAction($request)
    {
        $this->setKey($request[1]);
        $this->SetRequestAction('delete');
        $this->setComplete(TRUE);
    }

    protected function AddAction($request)
    {
        $this->SetRequestAction('add');
        $this->setKey($request[1]);

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

        //if memcache client send "value" data within one request, our request parser will save it in "data"
        if ($request['data']) {
            $this->pushData($request['data']);
        }
    }

    /**
     * Memcache "quit" Action
     *
     * @param array $request
     * @return void
     */
    protected function QuitAction($request)
    {
        $this->SetRequestAction('quit');
        $this->setComplete(TRUE);
    }

    /**
     * Method for validating "value" Data for "set" and "add" Action.
     * Check if Bytes value is reached and set State/Response
     *
     * @param $data
     * @return mixed
     */
    protected function pushData($data)
    { 
        if ($data == $this->getNewline() && strlen($this->getData()) == $this->getBytes()) {
            $this->setComplete(true);
            return true;
        } else {
            if ($data != $this->getNewLine()) {$data = rtrim($data);}
            $this->setData($data);
            if (strlen($this->getData()) == $this->getBytes()) {
                $this->setComplete(true);
                return true;
            } elseif (strlen($this->getData()) > $this->getBytes()) {
                throw new \Exception("CLIENT_ERROR bad data chunk");
            }
        }
    }
}