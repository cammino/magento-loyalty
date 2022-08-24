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
$installer->run(
    "DROP TABLE IF EXISTS customer_points;
    CREATE TABLE `customer_points` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `customer_id` int(11) NOT NULL,
      `order_id` int(11) NOT NULL,
      `direction` varchar(255) NOT NULL,
      `amount` decimal(12,4) NOT NULL,
      `points` int(11) NOT NULL,
      `money_to_point` float NOT NULL,
      `point_to_money` float NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `status` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;"
);

$installer->run("if exists (select * from information_schema.columns where table_name = `". $installer->getTable('sales/order') ."` and column_name = `loyaltytax`) then alter table `". $installer->getTable('sales/order') ."` drop column `loyaltytax`; end if;");
$installer->run("if exists (select * from information_schema.columns where table_name = `". $installer->getTable('sales/order') ."` and column_name = `loyaltytax`) then alter table `". $installer->getTable('sales/order') ."` drop column `base_loyaltytax`; end if;");
$installer->run("if exists (select * from information_schema.columns where table_name = `". $installer->getTable('sales/quote') ."` and column_name = `loyaltytax`) then alter table `". $installer->getTable('sales/quote') ."` drop column `loyaltytax`; end if;");
$installer->run("if exists (select * from information_schema.columns where table_name = `". $installer->getTable('sales/quote') ."` and column_name = `loyaltytax`) then alter table `". $installer->getTable('sales/quote') ."` drop column `base_loyaltytax`; end if;");
$installer->run("if exists (select * from information_schema.columns where table_name = `". $installer->getTable('sales/invoice') ."` and column_name = `loyaltytax`) then alter table `". $installer->getTable('sales/invoice') ."` drop column `loyaltytax`; end if;");
$installer->run("if exists (select * from information_schema.columns where table_name = `". $installer->getTable('sales/invoice') ."` and column_name = `loyaltytax`) then alter table `". $installer->getTable('sales/invoice') ."` drop column `base_loyaltytax`; end if;");

$installer->run("ALTER TABLE `". $installer->getTable('sales/order') ."` ADD `loyaltytax` DECIMAL(12,4) NULL");
$installer->run("ALTER TABLE `". $installer->getTable('sales/order') ."` ADD `base_loyaltytax` DECIMAL(12,4) NULL");
$installer->run("ALTER TABLE `". $installer->getTable('sales/quote') ."` ADD `loyaltytax` DECIMAL(12,4) NULL");
$installer->run("ALTER TABLE `". $installer->getTable('sales/quote') ."` ADD `base_loyaltytax` DECIMAL(12,4) NULL");
$installer->run("ALTER TABLE `". $installer->getTable('sales/invoice') ."` ADD `loyaltytax` DECIMAL(12,4) NULL");
$installer->run("ALTER TABLE `". $installer->getTable('sales/invoice') ."` ADD `base_loyaltytax` DECIMAL(12,4) NULL");

$installer->endSetup();