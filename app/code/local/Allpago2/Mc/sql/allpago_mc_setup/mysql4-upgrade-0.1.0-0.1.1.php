<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->exec("ALTER TABLE {$this->getTable('allpago_mc/log')} MODIFY order_id  INT(10) UNSIGNED DEFAULT NULL");

$installer->endSetup();

