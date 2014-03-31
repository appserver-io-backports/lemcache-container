<?php

/**
 * TechDivision\LemCacheContainer\Container
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
 * @author    Johann Zelger <jz@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\Interfaces\ContainerInterface;

/**
 * Class Container
 *
 * @category  Appserver
 * @package   TechDivision_LemCacheContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class CacheContainer extends \Stackable implements ContainerInterface
{
    /**
     * Initializes the container with the initial context, the unique container ID
     * and the deployed applications.
     *
     * @param \TechDivision\ApplicationServer\InitialContext                         $initialContext The initial context
     * @param \TechDivision\ApplicationServer\Api\Node\ContainerNode                 $containerNode  The container's UUID
     * @param array<\TechDivision\ApplicationServer\Interfaces\ApplicationInterface> $applications   The application instance
     *
     * @return void
     */
    public function __construct($initialContext, $containerNode, $applications)
    {
        $this->initialContext = $initialContext;
        $this->containerNode = $containerNode;
        $this->applications = $applications;
    }

    /**
     * Returns the receiver instance ready to be started.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\ReceiverInterface The receiver instance
     */
    public function getReceiver()
    {
        // nothing
    }

    /**
     * Returns an array with the deployed applications.
     *
     * @return array The array with applications
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Return's the containers config node
     *
     * @return \TechDivision\ApplicationServer\Api\Node\ContainerNode
     */
    public function getContainerNode()
    {
        return $this->containerNode;
    }

    /**
     * Return's the initial context instance
     *
     * @return \TechDivision\ApplicationServer\InitialContext
     */
    public function getInitialContext()
    {
        return $this->initialContext;
    }

    /**
     * Run the containers logic
     *
     * @return void
     */
    public function run()
    {
        // define webservers base dir
        // todo: refactor this in webserver repository
        define(
            'WEBSERVER_BASEDIR',
            $this->getInitialContext()->getSystemConfiguration()->getBaseDirectory()->getNodeValue()->__toString()
            . DIRECTORY_SEPARATOR
        );
        define(
            'WEBSERVER_AUTOLOADER',
            WEBSERVER_BASEDIR .
            'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'
        );

        // setup configurations
        $serverConfigurations = array();
        foreach ($this->getContainerNode()->getServers() as $serverNode) {
            $serverConfigurations[] = new ServerNodeConfiguration($serverNode);
        }

        // init server array
        $servers = array();

        // start servers by given configurations
        foreach ($serverConfigurations as $serverConfig) {

            // get type definitions
            $serverType = $serverConfig->getType();
            $serverContextType = $serverConfig->getServerContextType();

            // create a new instance server context
            $serverContext = new $serverContextType();

            // inject container to be available in specific mods etc. and initialize the module
            $serverContext->injectContainer($this);
            $serverContext->init($serverConfig);

            // init and start server
            $servers[] = new $serverType($serverContext);
        }

        // wait for servers
        foreach ($servers as $server) {
            $server->join();
        }
    }
}
