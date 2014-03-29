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

/**
 * Abstract implementation to handle memcache functionality.
 * 
 * @category   Appserver
 * @package    TechDivision_WebSocketContainer
 * @subpackage Api
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class AbstractMemCache
{

    /**
     * Holds the request value object instance.
     *
     * @var \TechDivision\LemCacheContainer\Api\MemCacheEntry
     */
    protected $vo = null;

    /**
     * The string representing a the new line char.
     *
     * @var string
     */
    protected $newLine = "\r\n";

    /**
     * Prefix for saving multiple keys inside one Stackable.
     * 
     * @var string
     */
    protected $storePrefix = "0-";

    /**
     * Keeps the garbage collector prefix value.
     *
     * @var string
     */
    protected $gcPrefix = "1";

    /**
     * Keeps response text that will sent to client after finish processing request.
     *
     * @var string
     */
    protected $response = "";

    /**
     * Keeps the following state of the connection values are: resume, reset, close.
     *
     * @var string
     */
    protected $state = "close";

    /**
     * Flag is action is already and only data are expected.
     *
     * @var boolean
     */
    protected $action = false;

    /**
     * Memcache flag value to enable/disable compression.
     *
     * @var integer
     */
    protected $flags = 0;

    /**
     * Seconds after a entry is invalid.
     *
     * @var integer
     */
    protected $expTime = 0;

    /**
     * Value length in bytes.
     *
     * @var integer
     */
    protected $bytes = 0;

    /**
     * holds the value to be stored.
     *
     * @var string
     */
    protected $data = "";

    /**
     * Holds the key of the value to be stored.
     *
     * @var string
     */
    protected $key = "";

    /**
     * Returns the value object instance.
     *
     * @return \TechDivision\LemCacheContainer\Api\MemCacheEntry
     */
    protected function getVO()
    {
        return $this->vo;
    }

    /**
     * Returns the response that will be sent back to the client.
     *
     * @return string The response that will be sent back
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response that will be sent back to the client.
     *
     * @param string $response The response to sent back
     * 
     * @return void
     */
    protected function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Return's following state of the connection, one of resume, 
     * reset or close.
     *
     * @return string The state itself
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set's following state of the connection, one of resume, reset or close.
     *
     * @param string $var The cache state
     * 
     * @return void
     */
    protected function setState($var)
    {
        $this->state = $var;
    }

    /**
     * Set's the cache flags.
     *
     * @param integer $flags The cache flag to be set
     * 
     * @return void
     */
    protected function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * Return's the cache flags.
     *
     * @return integer The cache flags
     */
    protected function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set's the number of the bytes of the data.
     *
     * @param integer $bytes The number of bytes
     * 
     * @return void
     */
    protected function setBytes($bytes)
    {
        $this->bytes = $bytes;
    }

    /**
     * Return's the number of bytes of the data.
     *
     * @return integer The nubmer of bytes
     */
    protected function getBytes()
    {
        return $this->bytes;
    }

    /**
     * Set's the expriation time for the data in seconds.
     *
     * @param integer $expTime The data's expiration time in seconds
     * 
     * @return void
     */
    protected function setExpTime($expTime)
    {
        $this->expTime = $expTime;
    }

    /**
     * Return's the expiration time for the data in seconds.
     *
     * @return integer The data's expiration time in seconds
     */
    protected function getExpTime()
    {
        return $this->expTime;
    }

    /**
     * The new line value used.
     *
     * @return string The new line value
     */
    protected function getNewLine()
    {
        return $this->newLine;
    }

    /**
     * Appends the data to this instance.
     *
     * @param string $data The data to append
     *  
     * @return void
     */
    protected function setData($data)
    {
        $this->data .= $data;
    }

    /**
     * Returns the data of this instance.
     *
     * @return string The instance data
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * Set's the key of the value to be stored.
     *
     * @param string $key The key of the value to be stored
     * 
     * @return void
     */
    protected function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Return's the key of the value to be stored.
     *
     * @return string The key of the value to be stored
     */
    protected function getKey()
    {
        return $this->key;
    }

    /**
     * Return's the store instance.
     *
     * @return \Stackable The store instance itself
     */
    protected function getStore()
    {
        return $this->store;
    }

    /**
     * Check if action value is set.
     *
     * @return boolean TRUE if the action has been set, else FALSE
     */
    protected function isAction()
    {
        return $this->action;
    }

    /**
     * Set action attribute to TRUE (disabling is not important).
     *
     * @return void
     */
    protected function setIsAction()
    {
        $this->action = true;
    }

    /**
     * Return's the store prefix.
     *
     * @return string The store prefix
     */
    protected function getStorePrefix()
    {
        return $this->storePrefix;
    }

    /**
     * Return's the garbage collector prefix.
     *
     * @return string The garbage collector prefix
     */
    protected function getGCPrefix()
    {
        return $this->gcPrefix;
    }

    /**
     * Reset all attributes for reusing the object.
     *
     * @return void
     */
    public function reset()
    {
        $this->newLine = "\r\n";
        $this->response = "";
        $this->state = "reset";
        $this->action = false;
        $this->flags = 0;
        $this->expTime = 0;
        $this->bytes = 0;
        $this->data = "";
        $this->key = "";
        $this->vo = null;
    }

    /**
     * Returns the value with the passed key from the store.
     *
     * @param string $key The key to return the value for
     * 
     * @return string The value for the passed key
     */
    protected function storeGet($key)
    {
        $result = "";
        \Mutex::lock($this->mutex);
        $s = $this->store[$this->getStorePrefix() . $key];
        \Mutex::unlock($this->mutex);
        if ($s) {
            $result = "VALUE " . $s['key'] . " ";
            $result .= $s['flags'] . " ";
            $result .= $s['bytes'] . $this->getNewLine();
            $result .= $s['value'] . $this->getNewLine();
        }
        $result .= "END";
        return $result;
    }

    /**
     * Checks if the passed key already exists in store.
     *
     * @param string $key The key to check for
     * 
     * @return boolean TRUE if the value has already been stored, else FALSE
     */
    protected function storeKeyExists($key)
    {
        \Mutex::lock($this->mutex);
        $s = $this->store[$this->getStorePrefix() . $key];
        \Mutex::unlock($this->mutex);
        if ($s) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds a new value build from the passed data in store.
     *
     * @param string  $key     The key to store the value with 
     * @param integer $flags   Flags to compress/uncompress the value
     * @param integer $expTime The expiration time in seconds
     * @param integer $bytes   The bytes of the value
     * @param string  $value   The value itself
     * 
     * @return boolean TRUE if the value has been added, else FALSE
     * @todo Refactor invalidator code because of problems with stackable array (line 429+)
     */
    protected function storeAdd($key, $flags, $expTime, $bytes, $value)
    {

        // check if the data has already been set in cache
        if (!$this->storeKeyExists($key)) {
            return $this->storeSet($key, $flags, $expTime, $bytes, $value);
        }
        
        // return FALSE if the data has already been found in cache
        return false;
    }

    /**
     * Store a new value build from the passed data in store.
     *
     * @param string  $key     The key to store the value with 
     * @param integer $flags   Flags to compress/uncompress the value
     * @param integer $expTime The expiration time in seconds
     * @param integer $bytes   The bytes of the value
     * @param string  $value   The value itself
     * 
     * @return boolean TRUE if the value has been added, else FALSE
     * @todo Refacotr invalidator code because of problems with stackable array (line 429+)
     */
    protected function storeSet($key, $flags, $expTime, $bytes, $value)
    {
        
        // initialize the array with the data
        $ar = array();
        $ar['key'] = $key;
        $ar['flags'] = $flags;
        $ar['exptime'] = $expTime;
        $ar['bytes'] = $bytes;
        $ar['value'] = $value;

        // lock the container and try to store the data
        \Mutex::lock($this->mutex);
        $this->store[$this->getStorePrefix() . $key] = $ar;
        // add for every new entry a garbage collector Entry - another thread will keep a eye on it
        $invalidator = $this->store[$this->getGCPrefix()];
        $invalidator[$key] = $expTime;
        $this->store[$this->getGCPrefix()] = $invalidator;
        \Mutex::unlock($this->mutex);
        
        // return TRUE if the data has been stored successfully
        return true;
    }

    /**
     * Delete's the value with the passed key from the store.
     *
     * @param string $key The key of the value to delete
     * 
     * @return string The result as string
     */
    protected function storeDelete($key)
    {
        \Mutex::lock($this->mutex);
        if ($this->store[$this->getStorePrefix() . $key]) {
            unset($this->store[$this->getStorePrefix() . $key]);
            $result = "DELETED";
        } else {
            $result = "NOT_FOUND";
        }
        \Mutex::unlock($this->mutex);
        return $result;
    }

    /**
     * Return's entry from store in raw (array) format.
     *
     * @param string $key The key to return the entry for
     * 
     * @return array The entry itself
     */
    protected function storeRawGet($key)
    {
        \Mutex::lock($this->mutex);
        $s = $this->store[$this->getStorePrefix() . $key];
        \Mutex::unlock($this->mutex);
        return $s;
    }

    /**
     * Set's entry from store in raw (array) format.
     *
     * @param array $ar The array with the key to return the value for
     *  
     * @return void
     */
    protected function storeRawSet($ar)
    {
        \Mutex::lock($this->mutex);
        $this->store[$this->getStorePrefix() . $ar['key']] = $ar;
        \Mutex::unlock($this->mutex);
    }
    
    /**
     * The memcache "incr" action (that increments the variable with the passed key by +1).
     *
     *
     * @param string      $key   The key of the value to increment
     * @param string|null $value The value to increment if value is not a number
     * 
     * @return void
     */
    protected function storeIncrement($key, $newValue = null)
    {
        
        \Mutex::lock($this->mutex);
        
        if ($this->store[$this->getStorePrefix() . $key]) {
            
            $value = $this->store[$this->getStorePrefix() . $key];
            
            if (is_numeric($value)) {
                $this->store[$this->getStorePrefix() . $key] = $value + 1;
            } else {
                $this->store[$this->getStorePrefix() . $key] = $newValue;
            }
            
            $result = $this->store[$this->getStorePrefix() . $key];
            
        } else {
            $result = "NOT_FOUND";
        }
        
        \Mutex::unlock($this->mutex);
        
        return $result;
    }
    
    /**
     * The memcache "decr" action (that decrements the variable with the passed key by -1).
     *
     *
     * @param string      $key   The key of the value to decrement
     * @param string|null $value The value to increment if value is not a number
     * 
     * @return void
     */
    protected function storeDecrement($key, $newValue = null)
    {
        
        \Mutex::lock($this->mutex);
        
        if ($this->store[$this->getStorePrefix() . $key]) {
            
            $value = $this->store[$this->getStorePrefix() . $key];
            
            if (is_numeric($value) && $value > 0) {
                $this->store[$this->getStorePrefix() . $key] = $value + 1;
            } else {
                $this->store[$this->getStorePrefix() . $key] = $newValue;
            }
            
            $result = $this->store[$this->getStorePrefix() . $key];
            
        } else {
            $result = "NOT_FOUND";
        }
        
        \Mutex::unlock($this->mutex);
        
        return $result;
    }
}
