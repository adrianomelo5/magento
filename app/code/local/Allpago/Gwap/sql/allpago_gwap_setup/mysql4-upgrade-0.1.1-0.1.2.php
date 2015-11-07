<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('gwap/oneclick')} ADD cc_last4 VARCHAR(10) AFTER registration_info");

$installer->endSetup();