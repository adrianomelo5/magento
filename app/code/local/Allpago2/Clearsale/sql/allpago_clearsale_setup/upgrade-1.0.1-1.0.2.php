<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE  {$this->getTable('clearsale/orders')} ADD score varchar(10) NULL AFTER status_clearsale;
");

$installer->endSetup();
