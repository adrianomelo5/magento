<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD capture_result text");
$installer->run("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD capture_result2 text");

$installer->endSetup();