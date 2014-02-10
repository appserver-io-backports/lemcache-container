<?php

/**
 * TechDivision\LemCacheContainer\Socket\CliClient
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

namespace TechDivision\LemCacheContainer\Socket;

use TechDivision\Socket\Client;

/**
 * Command line socket client implementation.
 * 
 * @category   Appserver
 * @package    TechDivision_WebSocketContainer
 * @subpackage Socket
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class CliClient extends Client
{

    /**
     * The command line new line char.
     * 
     * @var string
     */
    protected $newLine = "\r\n";

    /**
     * Reads from the socket as long as the new line char has been found
     * and returns the data.
     * 
     * @return void
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
