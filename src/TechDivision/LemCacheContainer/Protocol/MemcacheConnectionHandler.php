<?php
/**
 * \TechDivision\LemCacheContainer\Protocol\MemcacheConnectionHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Library
 * @package    TechDivision_LemCacheContainer
 * @subpackage Protocol
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_Http
 */

namespace TechDivision\LemCacheContainer\Protocol;

use TechDivision\WebServer\Dictionaries\ServerVars;
use TechDivision\WebServer\Exceptions\ModuleException;
use TechDivision\WebServer\Interfaces\ConnectionHandlerInterface;
use TechDivision\WebServer\Interfaces\ServerConfigurationInterface;
use TechDivision\WebServer\Interfaces\ServerContextInterface;
use TechDivision\WebServer\Interfaces\WorkerInterface;
use TechDivision\WebServer\Sockets\SocketInterface;
use TechDivision\WebServer\Sockets\SocketReadTimeoutException;
/**
 * Class HttpConnectionHandler
 *
 * @category   Library
 * @package    TechDivision_Memcache
 * @subpackage Protocol
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_Http
 */
class MemcacheConnectionHandler implements ConnectionHandlerInterface
{

    /**
     * The server context instance
     *
     * @var \TechDivision\WebServer\Interfaces\ServerContextInterface
     */
    protected $serverContext;

    /**
     * The connection instance
     *
     * @var \TechDivision\WebServer\Sockets\SocketInterface
     */
    protected $connection;

    /**
     * The worker instance
     *
     * @var \TechDivision\WebServer\Interfaces\WorkerInterface
     */
    protected $worker;

    /**
     * Hold's an array of modules to use for connection handler
     *
     * @var array
     */
    protected $modules;

    /**
     * Inits the connection handler by given context and params
     *
     * @param \TechDivision\WebServer\Interfaces\ServerContextInterface $serverContext The server's context
     * @param array                                                     $params        The params for connection handler
     *
     * @return void
     */
    public function init(ServerContextInterface $serverContext, array $params = null)
    {
        // set server context
        $this->serverContext = $serverContext;

        // register shutdown handler
        register_shutdown_function(array(&$this, "shutdown"));
    }

    /**
     * Injects all needed modules for connection handler to process
     *
     * @param array $modules An array of Modules
     *
     * @return void
     */
    public function injectModules($modules)
    {
        $this->modules = $modules;
    }

    /**
     * Return's all needed modules as array for connection handler to process
     *
     * @return array An array of Modules
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Return's the server context instance
     *
     * @return \TechDivision\WebServer\Interfaces\ServerContextInterface
     */
    public function getServerContext()
    {
        return $this->serverContext;
    }

    /**
     * Return's the server's configuration
     *
     * @return \TechDivision\WebServer\Interfaces\ServerConfigurationInterface
     */
    public function getServerConfig()
    {
        return $this->getServerContext()->getServerConfig();
    }

    /**
     * Return's the connection used to handle with
     *
     * @return \TechDivision\WebServer\Sockets\SocketInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Return's the worker instance which starte this worker thread
     *
     * @return \TechDivision\WebServer\Interfaces\WorkerInterface
     */
    protected function getWorker()
    {
        return $this->worker;
    }

    /**
     * Handles the connection with the connected client in a proper way the given
     * protocol type and version expects for example.
     *
     * @param \TechDivision\WebServer\Sockets\SocketInterface    $connection The connection to handle
     * @param \TechDivision\WebServer\Interfaces\WorkerInterface $worker     The worker how started this handle
     *
     * @return bool Weather it was responsible to handle the firstLine or not.
     */
    public function handle(SocketInterface $connection, WorkerInterface $worker)
    {

        // add connection ref to self
        $this->connection = $connection;
        $this->worker = $worker;

        // get instances for short calls
        $serverContext = $this->getServerContext();
        $serverConfig = $serverContext->getServerConfig();

        // send response to connected client
        $this->sendResponse();

        // init server vars
        $serverContext->initServerVars();

        // finally close connection
        $connection->close();
    }

    /**
     * Send's response to connected client
     *
     * @return void
     */
    public function sendResponse()
    {
        // get local var refs
        $connection = $this->getConnection();
        
        // write response headers
        $connection->write("VERSION 0.6.0beta\r\n");
    }

    /**
     * Init's the server vars by parsed request
     *
     * @return void
     */
    public function initServerVars()
    {
        // get server context to local var reference
        $serverContext = $this->getServerContext();
    }

    /**
     * Does shutdown logic for worker if something breaks in process
     *
     * @return void
     */
    public function shutdown()
    {
        // get refs to local vars
        $connection = $this->getConnection();
        $worker = $this->getWorker();

        // check if connections is still alive
        if ($connection) {

            // send response before shutdown
            $this->sendResponse();

            // close client connection
            $this->getConnection()->close();
        }

        // check if worker is given
        if ($worker) {
            // call shutdown process on worker to respawn
            $this->getWorker()->shutdown();
        }
    }
}
