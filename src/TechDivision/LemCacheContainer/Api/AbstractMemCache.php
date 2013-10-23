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

/**
 * The http client implementation that handles the request like a webserver
 *
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */

class AbstractMemCache
{
    /**
     * keeps string representing a NEWLINE
     *
     * @var string
     */
    protected $newLine="\r\n";


    /**
     * Prefix for saving multiple keys inside one Stackable
     *
     */
    protected $storePrefix = "0-";

    /**
     * keeps response text that will sent to client after finish processing request
     *
     * @var string
     */
    protected $response = "";

    /**
     * keeps the following state of the connection
     * values are: resume, reset, close
     *
     * @var string
     */
    protected $state = "close";

    /**
     * flag is action is already and only Data are expected
     *
     * @var bool
     */
    protected $action = FALSE;

    /**
     * MemCache Flag Value (enable/disable compression)
     *
     * @var int
     */
    protected $flags = 0;

    /**
     * Seconds after a Enty is InValid
     *
     * @var int
     */
    protected $expTime = 0;

    /**
     * Value length in bytes
     *
     * @var int
     */
    protected $bytes = 0;

    /**
     * holds "value"
     *
     * @var string
     */
    protected $data = "";

    /**
     * holds "key"
     *
     * @var string
     */
    protected $key = "";

    /**
     * reset all attributes for reusing object
     *
     * @return void
     */
    public function reset()
    {
        $this->newLine="\r\n";
        $this->response = "";
        $this->state = "";
        $this->action = FALSE;
        $this->flags = 0;
        $this->expTime = 0;
        $this->bytes = 0;
        $this->data = "";
        $this->key = "";
    }

    /**
     * Get $response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set $response
     *
     * @param $response
     * @return void
     */
    protected function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * set $state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * set $state
     *
     * @param $var
     * @return void
     */
    protected function setState($var)
    {
        $this->state = $var;
    }

    /**
     * set $flags
     *
     * @param $flags
     * @return void
     */
    protected function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * get $flags
     *
     * @return int
     */
    protected function getFlags()
    {
        return $this->flags;
    }

    /**
     * set $bytes
     *
     * @param $bytes
     * @return void
     */
    protected function setBytes($bytes)
    {
        $this->bytes = $bytes;
    }

    /**
     * get $bytes
     *
     * @return int
     */
    protected function getBytes()
    {
        return $this->bytes;
    }

    /**
     * set $expTime
     *
     * @param $ExpTime
     * @return void
     */
    protected function setExpTime($ExpTime)
    {
        $this->expTime = $ExpTime;
    }

    /**
     * get $expTime
     *
     * @return int
     */
    protected function getExpTime()
    {
        return $this->expTime;
    }

    /**
     * get $newLine
     *
     * @return string
     */
    protected function getNewLine()
    {
        return $this->newLine;
    }

    /**
     * set $data ("value")
     *
     * @param $data
     * @return void
     */
    protected function setData($data)
    {
        $this->data .= $data;
    }

    /**
     * get $data
     *
     * @return string
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * set $key
     *
     * @param $key
     * @return void
     */
    protected function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * get $key
     *
     * @return string
     */
    protected function getKey()
    {
        return $this->key;
    }

    /**
     * get $store
     *
     * @return StackableArray
     */
    protected function getStore()
    {
        return $this->store;
    }

    /**
     * Check if action value is set
     *
     * @return bool
     */
    protected function isAction()
    {
        return $this->action;
    }

    /**
     * Set $action attribute to TRUE (disabling is not important)
     *
     * @return void
     */
    protected function SetIsAction()
    {
        $this->action = true;
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
     * getting Values from $store
     *
     * @param string $key
     * @return string
     */
    protected function StoreGet($key)
    {
        var_dump($this->store);
        $result = "";
        \Mutex::lock($this->mutex);
        $s = $this->store[$this->getStorePrefix().$key];
        \Mutex::unlock($this->mutex);
        if ($s) {
            $result = "VALUE ".$s['key']." ";
            $result .= $s['flags']." ";
            $result .= $s['bytes'].$this->getNewLine();
            $result .= $s['value'].$this->getNewLine();
        }
        $result .= "END";

        return $result;
    }

    /**
     * Setting new values in $store
     *
     * @param $key
     * @param $flags
     * @param $exptime
     * @param $bytes
     * @param $value
     * @return bool
     */
    protected function StoreSet($key, $flags, $exptime, $bytes, $value)
    {
        $ar = array();
        $ar['key'] = $key;
        $ar['flags'] = $flags;
        $ar['exptime'] = $exptime;
        $ar['bytes'] = $bytes;
        $ar['value'] = $value;

        \Mutex::lock($this->mutex);
        $this->store[$this->getStorePrefix().$key] = $ar;
        \Mutex::unlock($this->mutex);
        var_dump($this->store);
        return TRUE;
    }

    /**
     * Deleting values from $store
     *
     * @param $key
     * @return string
     */
    protected function StoreDelete($key)
    {
        \Mutex::lock($this->mutex);
        if ($this->store[$this->getStorePrefix().$key]) {
            unset($this->store[$this->getStorePrefix().$key]);
            $result = "DELETED";
        } else {
            $result = "NOT_FOUND";
        }
        \Mutex::unlock($this->mutex);
        return $result;
    }
}