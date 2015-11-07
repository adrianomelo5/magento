<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE allpago_payment_orders DROP FOREIGN KEY FK_ALLPAGO_PAYMENT_ORDER_ID_ENTITY_ID");

$installer->endSetup();

