<?php
/**
 * \TechDivision\LemCacheContainer\ServerNodeConfiguration
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

use TechDivision\ApplicationServer\Api\Node\NodeInterface;
use TechDivision\WebServer\Interfaces\ServerConfigurationInterface;

/**
 * Class ServerNodeConfiguration
 *
 * @category  Appserver
 * @package   TechDivision_LemCacheContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class ServerNodeConfiguration implements ServerConfigurationInterface
{

    /**
     * Hold's node instance
     *
     * @var \TechDivision\ApplicationServer\Api\Node\NodeInterface
     */
    protected $node;

    /**
     * Hold's the handlers array
     *
     * @var array
     */
    protected $handlers;

    /**
     * Hold's the connection handler array
     *
     * @var array
     */
    protected $connectionHandlers;

    /**
     * Hold's the virtual hosts array
     *
     * @var array
     */
    protected $virtualHosts;

    /**
     * Hold's the authentications array
     *
     * @var array
     */
    protected $authentications;

    /**
     * Hold's the modules array
     *
     * @var array
     */
    protected $modules;

    /**
     * Holds the rewrites array
     *
     * @var array
     */
    protected $rewrites = array();

    /**
     * Holds the environmentVariables array
     *
     * @var array
     */
    protected $environmentVariables = array();

    /**
     * Constructs config
     *
     * @param \TechDivision\ApplicationServer\Api\Node\NodeInterface $node The node instance
     */
    public function __construct($node)
    {
        $this->node = $node;
    }

    /**
     * Returns servers name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->node->getName();
    }

    /**
     * Returns logger name.
     *
     * @return string
     */
    public function getLoggerName()
    {
        return $this->node->getLoggerName();
    }

    /**
     * Return's type
     *
     * @return string
     */
    public function getType()
    {
        return $this->node->getType();
    }

    /**
     * Return's transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->node->getParam('transport');
    }

    /**
     * Return's address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->node->getParam('address');
    }

    /**
     * Return's port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->node->getParam('port');
    }

    /**
     * Return's software
     *
     * @return string
     */
    public function getSoftware()
    {
        return $this->node->getParam('software');
    }

    /**
     * Return's admin
     *
     * @return string
     */
    public function getAdmin()
    {
        return $this->node->getParam('admin');
    }

    /**
     * Return's keep-alive max connection
     *
     * @return int
     */
    public function getKeepAliveMax()
    {
        return (int)$this->node->getParam('keepAliveMax');
    }

    /**
     * Return's keep-alive timeout
     *
     * @return int
     */
    public function getKeepAliveTimeout()
    {
        return (int)$this->node->getParam('keepAliveTimeout');
    }

    /**
     * Return's template file path for errors page
     *
     * @return string
     */
    public function getErrorsPageTemplatePath()
    {
        return (string)$this->node->getParam('errorsPageTemplatePath');
    }

    /**
     * Return's worker number
     *
     * @return int
     */
    public function getWorkerNumber()
    {
        return $this->node->getParam('workerNumber');
    }

    /**
     * Return's context type
     *
     * @return string
     */
    public function getServerContextType()
    {
        return $this->node->getServerContext();
    }

    /**
     * Return's socket type
     *
     * @return string
     */
    public function getSocketType()
    {
        return $this->node->getSocket();
    }

    /**
     * Return's worker type
     *
     * @return string
     */
    public function getWorkerType()
    {
        return $this->node->getWorker();
    }

    /**
     * Return's document root
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->node->getParam('documentRoot');
    }

    /**
     * Return's connection handlers
     *
     * @return array
     */
    public function getConnectionHandlers()
    {
        if (!$this->connectionHandlers) {
            foreach ($this->node->getConnectionHandlers() as $connectionHandler) {
                $this->connectionHandlers[$connectionHandler->getUuid()] = $connectionHandler->getType();
            }
        }

        return $this->connectionHandlers;
    }

    /**
     * Return's modules
     *
     * @return array
     */
    public function getModules()
    {
        return array();
    }

    /**
     * Return's handlers
     *
     * @return array
     */
    public function getHandlers()
    {
        return array();
    }

    /**
     * Return's virtual hosts
     *
     * @return array
     */
    public function getVirtualHosts()
    {
        return array();
    }

    /**
     * Returns the authentication configuration.
     *
     * @return array The array with the authentication configuration
     */
    public function getAuthentications()
    {
        return array();
    }

    /**
     * Return's cert path
     *
     * @return string
     */
    public function getCertPath()
    {
        return '';
    }

    /**
     * Return's passphrase
     *
     * @return string
     */
    public function getPassphrase()
    {
        return '';
    }

    /**
     * Returns the rewrite configuration.
     *
     * @return array
     */
    public function getRewrites()
    {
        return array();
    }

    /**
     * Returns the environment variable configuration
     *
     * @return array
     */
    public function getEnvironmentVariables()
    {
        return array();
    }

    /**
     * Returns the rewrite configuration.
     *
     * @return array
     */
    public function getAccesses()
    {
        return array();
    }

    /**
     * Returns the rewrite configuration.
     *
     * @return array
     */
    public function getDirectoryIndex()
    {
        return array();
    }
}
