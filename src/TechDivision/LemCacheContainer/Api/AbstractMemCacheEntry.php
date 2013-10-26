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
    protected $action = FALSE;

    /**
     * Holds completion state of the Request
     *
     */
    protected $complete = FALSE;

    /**
     * Holds validity State of the Request
     *
     */
    protected $valid = TRUE;

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

    protected $requestAction;

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
     * keeps string representing a NEWLINE
     *
     * @var string
     */
    protected $newLine="\r\n";

    /**
     * get $newLine
     *
     * @return string
     */

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

    protected function setRequestAction($value)
    {
        $this->requestAction = $value;
    }

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
    protected function getBytes()
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
    protected function getKey()
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
    protected function getExpTime()
    {
        return $this->expTime;
    }

    protected function getNewLine()
    {
        return $this->newLine;
    }

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
     * @param $value
     */
    protected function setComplete($value)
    {
        $this->complete = $value;
    }

    protected function getComplete()
    {
        return $this->complete;
    }

    protected function setValidity($value)
    {
        $this->valid = $value;
    }

    protected function getValidity()
    {
        return $this->valid;
    }
}