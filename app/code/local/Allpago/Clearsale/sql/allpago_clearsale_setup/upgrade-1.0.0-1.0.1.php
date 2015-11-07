<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE  {$this->getTable('sales_flat_order')} ADD  `prazo_entrega` varchar(50) NULL;
");

$installer->endSetup();
