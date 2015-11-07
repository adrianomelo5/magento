<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('gwap/oneclick')} (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
      `registration_info` VARCHAR(64),
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      KEY `IDX_ALLPAGO_ONECLICK_CUSTOMER_ID` (`customer_id`),
      CONSTRAINT `FK_ALLPAGO_ONECLICK_CUSTOMER_ENTITY` FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();