<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('gwap/oneclick')} change registration_info registration_id VARCHAR(100)");
$installer->run("ALTER TABLE {$this->getTable('gwap/oneclick')} ADD type VARCHAR(20) AFTER cc_last4");

$installer->endSetup();