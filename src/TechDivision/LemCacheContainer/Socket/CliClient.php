<?php

/**
 * TechDivision\LemCacheContainer\Socket\CliClient
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\LemCacheContainer\Socket;

use TechDivision\Socket\Client;

/**
 *
 * @package     TechDivision\LemCacheContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */
class CliClient extends Client
{

    protected $newLine = "\r\n";

    /**
     * @see \TechDivision\ServletContainer\Interfaces\HttpClientInterface::receive()
     */
    public function receive()
    {
        // initialize the buffer
        $buffer = null;
        // read a chunk from the socket
        while ($buffer .= $this->read($this->getLineLength())) {
            if (false !== strpos($buffer, $this->getNewLine())) {
                return $buffer;
            }
        }
    }
}