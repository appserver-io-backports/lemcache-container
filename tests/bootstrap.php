<?php
/**
 * TechDivision\LemCacheContainer\bootstrap
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    TechDivision\LemCacheContainer
 * @subpackage Api
 * @author     René Rösner <r.roesner@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

$loader = require '${php-target.dir}/vendor/autoload.php';
$loader->add('TechDivision\\LemCacheContainer\\', '${php-target.dir}/${unique.name}/src');
