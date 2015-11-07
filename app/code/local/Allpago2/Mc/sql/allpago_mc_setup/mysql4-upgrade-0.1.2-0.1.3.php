<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->exec("ALTER TABLE {$this->getTable('allpago_mc/payment')} ADD registration_cc text");

$installer->endSetup();