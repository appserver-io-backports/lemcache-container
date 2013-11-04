<?php
/**
 * TechDivision\LemCacheContainer\Api\AbstractMemCacheEntry
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\LemCacheContainer\Api;

/**
 *
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */

class AbstractMemCacheEntry
{
    /**
     * keeps response text that will sent to client after finish processing request
     *
     * @var string
     */
    protected $response = "";

    /**
     * flag is action is already and only Data are expected
     *
     * @var bool
     */
    protected $action;

    /**
     * Holds completion state of the Request
     *
     */
    protected $complete;

    /**
     * MemCache Flag Value (enable/disable compression)
     *
     * @var int
     */
    protected $flags;

    /**
     * Seconds after a Enty is InValid
     *
     * @var int
     */
    protected $expTime;

    /**
     * Value length in bytes
     *
     * @var int
     */
    protected $bytes;

    /**
     * Holds MemCache CommandAction for this Request (e.g. "set")
     *
     * @var string
     */
    protected $requestAction;

    /**
     * holds "value"
     *
     * @var string
     */
    protected $data;

    /**
     * holds "key"
     *
     * @var string
     */
    protected $key;

    /**
     * keeps string representing a NEWLINE
     *
     * @var string
     */
    protected $newLine;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        //resets all attributes to default values
        $this->reset();
    }

    /**
     * resets all Attributes to default
     *
     * @return void
     */
    public function reset()
    {
        $this->response = "";
        $this->complete = FALSE;
        $this->flags = 0;
        $this->expTime = 0;
        $this->bytes = 0;
        $this->requestAction = "";
        $this->data = "";
        $this->key = "";
        $this->newLine="\r\n";
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
    public function getData()
    {
        return $this->data;
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
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * set RequestAction
     *
     * @param $value
     * @return void
     */
    protected function setRequestAction($value)
    {
        $this->requestAction = $value;
    }

    /**
     * get RequestAction
     *
     * @return string
     */
    public function getRequestAction()
    {
        return $this->requestAction;
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
    public function getBytes()
    {
        return $this->bytes;
    }

    /**
     * set $bytes
     *
     * @param $key
     * @return void
     */
    protected function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * get $bytes
     *
     * @return int
     */
    public function getKey()
    {
        return $this->key;
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
    public function getExpTime()
    {
        return $this->expTime;
    }

    /**
     * NewLine Value
     *
     * @return string
     */
    protected function getNewLine()
    {
        return $this->newLine;
    }

    /**
     * Set Request state (TRUE|FALSE)
     *
     * @param $value bool
     * @return void
     */
    protected function setComplete($value)
    {
        $this->complete = $value;
    }

    /**
     * get current request state
     *
     * @return mixed
     */
    protected function getComplete()
    {
        return $this->complete;
    }

    /**
     *
     *
     * @return mixed
     */
    public function isComplete()
    {
        return $this->getComplete();
    }
}