<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('clearsale/orders')} (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(10) unsigned NOT NULL DEFAULT '0',
      `increment_id` varchar(50) NULL,
      `status` varchar(255) DEFAULT NULL,
      `status_clearsale` varchar(255) DEFAULT NULL,
      `error_message` text NULL,
      `tries` int(1) unsigned DEFAULT '0',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      KEY `IDX_SALES_FLAT_ORDER_PAYMENT_PARENT_ID_CLEARSALE` (`order_id`),
      CONSTRAINT `FK_CLEARSALE_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES {$this->getTable('sales_flat_order')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");  

$installer->endSetup();
