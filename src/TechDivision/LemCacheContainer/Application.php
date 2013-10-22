<?php

/**
 * TechDivision\LemCacheContainer\Application
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
    
namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\AbstractApplication;

/**
 * The application instance holds all information about the deployed application
 * and provides a reference to the entity manager and the initial context.
 *
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <pd@techdivision.com>
 */
class Application extends AbstractApplication
{
    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     * 
     * @return \TechDivision\LemCacheContainer\Application The connected application
     */
    public function connect() {
        return $this;
    }
}