<?php

/**
 * Allpago Module for Fcontrol
 *
 * @title      Magento -> Custom Module for Fcontrol
 * @category   Fraud Control Gateway
 * @package    Allpago_Fcontrol
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('fcontrol/orders')} (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(10) unsigned NOT NULL DEFAULT '0',
      `status` varchar(255) DEFAULT NULL,
      `status_fcontrol` varchar(255) DEFAULT NULL,
      `error_code` varchar(20) DEFAULT NULL,
      `error_message` varchar(255) DEFAULT NULL,
      `info` text,
      `tries` int(1) unsigned DEFAULT '0',
      `abandoned` int(1) unsigned DEFAULT '0',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      KEY `IDX_SALES_FLAT_ORDER_PAYMENT_PARENT_ID_ANTIFRAUD` (`order_id`),
      CONSTRAINT `FK_ANTIFRAUD_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES {$this->getTable('sales_flat_order')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
      
$installer->endSetup();