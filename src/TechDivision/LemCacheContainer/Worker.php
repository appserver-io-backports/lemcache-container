<?php

/**
 * TechDivision\LemCacheContainer\Worker
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\ApplicationServer\AbstractContextThread;


/**
 * accepting incoming connections and making all the work
 *
 * @package TechDivision\LemCacheContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Philipp Dittert<p.dittert@techdivision.com>
 */
class Worker extends AbstractContextThread
{

    /**
     * StackableArray so share all Data between threads
     *
     * @var Stackable $store
     */
    public $store;

    /**
     * Mutex for locking access to $store for Data consistency
     *
     * @var Mutex
     */
    public $mutex;

    /**
     * Holds the container implementation
     *
     * @var ContainerInterface
     */
    public $container;

    /**
     * Holds the main socket resource
     *
     * @var resource
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
     * @param ContainerInterface $container
     *            A container implementation
     * @param resource $resource
     *            The client socket instance
     * @param string $threadType
     *            The thread type class to instantiate
     * @param Store $store
     *            StackableArray
     * @param Mutex $mutex
     *            Mutex for Stackable $store
     * @return void
     */
    public function init(ContainerInterface $container, $resource, $threadType,$store,$mutex)
    {
        $this->container = $container;
        $this->resource = $resource;
        $this->threadType = $threadType;
        $this->store = $store;
        $this->mutex = $mutex;
    }

    /**
     * @see \TechDivision\ApplicationServer\AbstractWorker::getResourceClass()
     */
    protected function getResourceClass()
    {
        return 'TechDivision\Socket';
    }

    /**
     *
     * @see \Thread::run()
     */
    public function main()
    {
        // create memcache api object
        $api = $this->newInstance('TechDivision\LemCacheContainer\Api\MemCache', array($this->store, $this->mutex));

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
}