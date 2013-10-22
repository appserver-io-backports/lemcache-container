<?php

/**
 * TechDivision\LemCacheContainer\Deployment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\LemCacheContainer;

use TechDivision\ApplicationServer\AbstractDeployment;

/**
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Ditttert <pd@techdivision.com>
 */
class Deployment extends AbstractDeployment {

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\Server The server instance
     */
    public function deploy() {
        // return initialized applications
        return $this;
    }
}