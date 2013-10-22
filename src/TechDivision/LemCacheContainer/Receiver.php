<?php

/**
 * TechDivision\LemCacheContainer\Receiver
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\AbstractReceiver;
use TechDivision\LemCacheContainer\Store;

/**
 * starting a SocketServer and initiates worker
 *
 * @package TechDivision\LemCacheContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Philipp Dittert <pd@techdivivision.com>
 */

class Receiver extends AbstractReceiver
{

    /**
     * @var $store stackable object
     */
    public $store;

    /**
     * @var $mutex MUTEX
     */
    public $mutex;

    /**
     * Returns the resource class used to create a new socket.
     *
     * @return string The resource class name
     */
    protected function getResourceClass()
    {
        return 'TechDivision\Socket\Server';
    }

    /**
     * (non-PHPdoc)
     *
     * @see \TechDivision\ApplicationServer\AbstractReceiver::start()
     */
    public function start()
    {

        try {

            // create new Store Object
            $this->store = new Store;

            //create Mutex for KeyValueStore
            $this->mutex = \Mutex::create(false);

            /** @var \TechDivision\Socket\Client $socket */
            $socket = $this->newInstance($this->getResourceClass());

            // prepare the main socket and listen
            $socket->setAddress($this->getAddress())->setPort($this->getPort())->start();

            // check if resource been initiated
            if ($resource = $socket->getResource()) {

                // init worker number
                $worker = 0;
                // init workers array holder
                $workers = array();

                // open threads where accept connections
                while ($worker ++ < $this->getWorkerNumber()) {
                    $params = array($this->initialContext, $this->getContainer(), $socket->getResource(), $this->getThreadType(), $this->getStore(), $this->getMutex());


                    $workers[$worker] = $this->newInstance($this->getWorkerType(), $params );
                    $workers[$worker]->start();

                    #$workers[$worker] = $this->newWorker($socket->getResource());
                    // start thread async

                }

                return true;
            }

        } catch (\Exception $e) {
            error_log($e->__toString());
        }

        if (is_resource($resource)) {
            $socket->close();
        }

        return false;


    }

    protected function getStore()
    {
        return $this->store;
    }

    protected function getMutex()
    {
        return $this->mutex;
    }
}