<?php
/**
* mysql4-install-0.0.1.php
*
* @category Cammino
* @package  Cammino_Loyalty
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-loyalty
*/

$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `customer_points` ADD `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'");

$installer->endSetup();