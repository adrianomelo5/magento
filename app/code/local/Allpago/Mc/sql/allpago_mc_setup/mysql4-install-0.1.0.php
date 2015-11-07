<?php

/**
 * Allpago + Conversão Module
 *
 * @title      Magento -> + Conversão Module
 * @category   Payment Gateway
 * @package    Allpago_Mc
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('gwap/order')} (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(10) unsigned NOT NULL DEFAULT '0',
      `status` varchar(255) DEFAULT NULL,
      `status_gateway` varchar(255) DEFAULT NULL,
      `error_code` varchar(20) DEFAULT NULL,
      `error_message` varchar(255) DEFAULT NULL,
      `info` text,
      `tries` int(1) unsigned DEFAULT '0',
      `type` varchar(20) DEFAULT NULL,
      `abandoned` int(1) unsigned DEFAULT '0',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      KEY `IDX_SALES_FLAT_ORDER_PAYMENT_PARENT_ID_PAYMENT` (`order_id`),
      CONSTRAINT `FK_ALLPAGO_PAYMENT_ORDER_ID_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES {$this->getTable('sales_flat_order')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('allpago_mc/log')} (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(10) unsigned DEFAULT NULL,
      `robot` varchar(50) NOT NULL,
      `method` varchar(200) NOT NULL,
      `status` varchar(255) NOT NULL,
      `message` varchar(255) DEFAULT NULL,
      `message_gateway` text DEFAULT NULL,
      `datetime` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `IDX_SALES_FLAT_ORDER_PAYMENT_PARENT_ID_ROBOTS` (`order_id`),
      CONSTRAINT `FK_ALLPAGO_ROBOTS_ORDER_ID_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES {$this->getTable('sales_flat_order')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();