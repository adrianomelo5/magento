<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD cc_type VARCHAR(20) AFTER type");

$installer->endSetup();