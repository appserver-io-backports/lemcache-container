<?php

/**
 * TechDivision\LemCacheContainer\Receiver
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
namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\AbstractReceiver;
use TechDivision\ApplicationServer\Utilities\StateKeys;
use TechDivision\LemCacheContainer\Store;

/**
 * Starting a SocketServer and initiates worker.
 *
 * @category  Appserver
 * @package   TechDivision_WebSocketContainer
 * @author    Philipp Dittert <pd@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @author    Johann Zelger <jw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class Receiver extends AbstractReceiver
{

    /**
     * The store object from the initial context.
     *
     * @var \Stackable
     */
    public $store;

    /**
     * The mutex instance to lock/unlock the store.
     *
     * @var integer
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
     * The main method that start's the thread.
     * 
     * @return void
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
                    
                    // initialize the worker arguments
                    $params = array(
                        $this->initialContext,
                        $this->getContainer(),
                        $socket->getResource(),
                        $this->getThreadType(),
                        $this->getStore(),
                        $this->getMutex()
                    );
                    
                    // initialize and start the worker
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
                    sprintf(
                        'Successfully started receiver for container %s, listening on IP: %s Port: %s Number of workers started: %s, Workertype: %s',
                        $this->getContainer()->getContainerNode()->getName(),
                        $this->getAddress(),
                        $this->getPort(),
                        $this->getWorkerNumber(),
                        $this->getWorkerType()
                    )
                );
                
                // prepare the stop key
                $stopState = StateKeys::get(StateKeys::STOPPING);
                
                // collect garbage and free memory/sockets
                while ($stopState->equals($this->getInitialContext()->getAttribute(StateKeys::KEY)) === false) {
                    usleep(100000);
                }
                    
                // kill all workers
                foreach ($workers as $worker) {
                    $worker->kill();
                }
                
                // kill the garbage collector
                $gc->kill();
            }

        } catch (\Exception $e) {
            $this->getInitialContext()
                ->getSystemLogger()
                ->error($e->__toString());
        }

        // close the socket if still open
        if (is_resource($resource)) {
            $socket->close();
        }
        
        // log that the receiver has successfully been shutdown
        $this->getInitialContext()->getSystemLogger()->info(
            "Successfully stopped receiver " . $this->getContainer()->getContainerNode()->getName()
        );
        
        return false;
    }

    /**
     * Returns the store object from the initial context.
     *
     * @return \Stackable $store The store object from the initial context
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
