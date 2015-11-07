<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD increment_id VARCHAR(50) AFTER order_id");

$installer->endSetup();