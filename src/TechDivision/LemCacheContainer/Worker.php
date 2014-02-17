<?php

/**
 * TechDivision\LemCacheContainer\Worker
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
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\ApplicationServer\AbstractContextThread;

/**
 * Accepting incoming connections and making all the work
 *
 * @category  Appserver
 * @package   TechDivision_WebSocketContainer
 * @author    Philipp Dittert <pd@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class Worker extends AbstractContextThread
{

    /**
     * StackableArray so share all Data between threads
     *
     * @var \Stackable $store
     */
    public $store;

    /**
     * Mutex for locking access to $store for Data consistency
     *
     * @var integer
     */
    public $mutex;

    /**
     * Holds the container implementation
     *
     * @var \TechDivision\ApplicationServer\Interfaces\ContainerInterface
     */
    public $container;

    /**
     * Holds the main socket resource
     *
     * @var mixed
     */
    public $resource;

    /**
     * The thread implementation classname
     *
     * @var string
     */
    public $threadType;

    /**
     * Init acceptor with container and acceptable socket resource
     * and thread type class.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ContainerInterface $container  A container implementation
     * @param mixed                                                         $resource   The client socket instance
     * @param string                                                        $threadType The thread type class to instantiate
     * @param \Stackable                                                    $store      Stackable array
     * @param integer                                                       $mutex      Mutex for the stackable
     *            
     * @return void
     */
    public function init(ContainerInterface $container, $resource, $threadType, $store, $mutex)
    {
        $this->container = $container;
        $this->resource = $resource;
        $this->threadType = $threadType;
        $this->store = $store;
        $this->mutex = $mutex;
    }

    /**
     * Returns the resource socket class name.
     * 
     * @return string The resource socket class name
     * @see \TechDivision\ApplicationServer\AbstractWorker::getResourceClass()
     */
    protected function getResourceClass()
    {
        return 'TechDivision\Socket';
    }
    
    /**
     * The main method to start the thread.
     * 
     * @return void
     * @see \Thread:run()
     */
    public function main()
    {
        // create memcache api object
        $api = $this->newInstance('TechDivision\LemCacheContainer\Api\MemCache', array($this->store, $this->mutex));
        
        // create MemCache ValueObject for request parsing
        $vo = $this->newInstance('TechDivision\LemCacheContainer\Api\MemCacheEntry');

        while (true) {

            // reinitialize the server socket
            $serverSocket = $this->initialContext->newInstance($this->getResourceClass(), array(
                $this->resource
            ));

            // accept client connection and process the request
            if ($clientSocket = $serverSocket->accept()) {

                // initialize a new client socket
                $client = $this->newInstance('TechDivision\LemCacheContainer\Socket\CliClient');

                // set the client socket resource
                $client->setResource($clientSocket->getResource());

                while (true) {
                    
                    try {
                        
                        // read client message
                        $message = $client->receive();

                        // push message into ValueObject
                        $vo->push($message);

                        if ($vo->isComplete()) {
                            
                            $api->request($vo);

                            // send response to client (even if response is empty)
                            $this->send($client, $api->getResponse());

                            // select current state
                            switch ($api->getState()) {
                            
                                case "reset":
                                    $vo->reset();
                                    $api->reset();
                                    break;
                                    
                                case "close":
                                    $vo->reset();
                                    $api->reset();
                                    
                                    try {
                                        $client->shutdown();
                                        $client->close();
                                    } catch (\Exception $e) {
                                        $client->close();
                                    }
                                    
                                    unset($client);
                                    break 2;
                                    
                                default:
                                    $this->send($client, "SERVER ERROR unknown state");
                                    
                            }
                        }
                        
                    } catch (\Exception $e) {
                        
                        $vo->reset();
                        $api->reset();
                        $result = $e->getMessage();
                        
                        $this->getInitialContext()->getSystemLogger()->critical($e->__toString());
                        
                        if (!$result) {
                            $result = "ERROR";
                        }
                        
                        $this->send($client, $result);
                    }
                }
            }
        }
    }

    /**
     * Helper Method for sending data to client. Add new line on every 
     * response.
     *
     * @param mixed  $socket   The socket instance
     * @param string $response The string to send back to client extended with the new line char
     * 
     * @return void
     */
    public function send($socket, $response)
    {
        if ($response !== '') {
            $socket->send($response . "\r\n");
        }
    }
}
