<?php

/**
 * TechDivision\LemCacheContainer\ThreadRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\AbstractContextThread;
use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\Socket\Client;
use TechDivision\SplClassLoader;
use TechDivision\MessageQueueClient\Queue;

/**
 * The thread implementation that handles the request.
 *
 * @package     TechDivision\MessageQueue
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
class ThreadRequest extends AbstractContextThread {

    /**
     * StackableArray as Central Storage between threads
     * @var \Stackable
     */
    public $store;

    /**
     * Mutex to Lock Stackable
     * @var Mutex
     */
    public $mutex;

    /**
     * The message to process.
     * @var string
     */
    public $message;

    /**
     * Holds the container instance
     *
     * @var ContainerInterface
     */
    public $container;

    /**
     * Initializes the request with the client socket.
     *
     * @param ContainerInterface $container The ServletContainer
     * @param resource $resource The client socket instance
     * @return void
     */
    public function init(ContainerInterface $container, $resource,$store = FALSE, $mutex = FALSE) {
        $this->container = $container;
        $this->resource = $resource;
        $this->store = $store;
        $this->mutex = $mutex;
    }


    /**
     * @see AbstractThread::main()
     */
    public function main(){

        // initialize a new client socket
        $client = $this->newInstance('TechDivision\LemCacheContainer\Socket\CliClient');

        // set the client socket resource
        $client->setResource($this->resource);

        while(true)
        {
            if (!isset($api)) {
                // initiate new api object
                $api = $this->newInstance('TechDivision\LemCacheContainer\Api\MemCache', array($this->getStore(), $this->getMutex()));
            }

            // read client message
            $message = $client->receive();

            // push message into api object
            $api->push($message);

            // send response to client (even if response is empty)
            $this->send($client, $api->getResponse());

            // select current state
            switch ($api->getState()) {
                case "resume";
                    break;
                case "reset";

                    $api->reset();
                    break;
                case "close":
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
    }

    /**
     * Helper Method for sending data to client. Add NewLine on every response
     *
     * @param $socket resource
     * @param $response string sending to client
     */
    public function send($socket, $response){
        if($response !== "") {
            $socket->send($response."\r\n");
        }
    }

    /**
     * Returns the container instance.
     *
     * @return \TechDivision\PersistenceContainer\Container The container instance
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * Returns the array with the available applications.
     *
     * @return array The available applications
     */
    public function getApplications() {
        return $this->getContainer()->getApplications();
    }

    /**
     * Tries to find and return the application for the passed class name.
     *
     * @param \TechDivision\MessageQueueClient\Queue $queue
     *            The queue to find and return the application instance
     * @return \TechDivision\PersistenceContainer\Application The application instance
     * @throws \Exception Is thrown if no application can be found for the passed class name
     */
    public function findApplication($queue) {

        // iterate over all classes and check if the application name contains the class name
        foreach ($this->getApplications() as $name => $application) {
            if ($application->hasQueue($queue)) {
                return $application;
            }
        }

        // if not throw an exception
        throw new \Exception("Can\'t find application for '" . $queue->getName() . "'");
    }

    public function getStore()
    {
        return $this->store;
    }

    public function getMutex()
    {
        return $this->mutex;
    }
}