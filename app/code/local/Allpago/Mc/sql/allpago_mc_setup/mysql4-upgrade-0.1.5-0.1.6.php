<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD info2 text AFTER info");
$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD cc_type2 VARCHAR(20) after cc_type");
$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD clearsale_info text AFTER registration_cc");

$installer->endSetup();