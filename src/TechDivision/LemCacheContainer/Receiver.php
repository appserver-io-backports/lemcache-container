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
     * The store object from the initial context.
     *
     * @var object
     */
    public $store;

    /**
     * The mutex instance to lock/unlock the store.
     *
     * @var \Mutex
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

            // load the store object from the initial context
            $this->store = $this->getInitialContext()
                ->getStorage()
                ->getStorage();

            // create Mutex for KeyValueStore
            $this->mutex = \Mutex::create(false);

            /**
             *
             * @var \TechDivision\Socket\Client $socket
             */
            $socket = $this->newInstance($this->getResourceClass());

            // prepare the main socket and listen
            $socket->setAddress($this->getAddress())
                ->setPort($this->getPort())
                ->start();

            // check if resource been initiated
            if ($resource = $socket->getResource()) {

                // init worker number
                $worker = 0;
                // init workers array holder
                $workers = array();

                // open threads where accept connections
                while ($worker ++ < $this->getWorkerNumber()) {
                    $params = array(
                        $this->initialContext,
                        $this->getContainer(),
                        $socket->getResource(),
                        $this->getThreadType(),
                        $this->getStore(),
                        $this->getMutex()
                    );

                    $workers[$worker] = $this->newInstance($this->getWorkerType(), $params);
                    $workers[$worker]->start();
                }

                // start garbageCollctor Thread
                $gc = $this->newInstance("TechDivision\LemCacheContainer\GarbageCollector", array(
                    $this->store,
                    $this->mutex
                ));
                $gc->start();

                // log a message that the container has been started successfully
                $this->getInitialContext()->getSystemLogger()->info(
                    sprintf('Successfully started receiver for container %s, listening on IP: %s Port: %s Number of workers started: %s, Workertype: %s',
                    $this->getContainer()->getContainerNode()->getName(), $this->getAddress(), $this->getPort(),
                    $this->getWorkerNumber(), $this->getWorkerType()));

                return true;
            }

        } catch (\Exception $e) {
            $this->getInitialContext()
                ->getSystemLogger()
                ->error($e->__toString());
        }

        if (is_resource($resource)) {
            $socket->close();
        }

        return false;
    }

    /**
     * Returns the store object from the initial context.
     *
     * @return object $store The store object from the initial context
     */
    protected function getStore()
    {
        return $this->store;
    }

    /**
     * Returns the mutex.
     *
     * @return \Mutex|int The mutex instance
     */
    protected function getMutex()
    {
        return $this->mutex;
    }
}