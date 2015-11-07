<?php

/**
* 
* SuperEmpreendedor para Magento
* 
* @category     SuperEmpreendedor
* @packages     Base
* @copyright    Copyright (c) 2014 SuperEmpreendedor (http://www.superempreendedor.com/pagseguro)
* @version      1.16.2
* @license      http://www.superempreendedor.com/magento/licenca (Este arquivo Ã© propriedade do SuperEmpreendedor e nÃ£o pode ser copiado ou distribuÃ­do sem autorizaÃ§Ã£o.)
*
*/

class Ideasa_Base_CronController extends Mage_Core_Controller_Front_Action {

    private $logger;

    /**
     * Instantiate config
     */
    protected function _construct() {
        $this->logger = Ideasa_Base_Logger::getLogger(__CLASS__);
        parent::_construct();
    }

    public function indexAction() {
        $status = 'OK';
        $this->logger->info('Cron - Início');
        try {
            Mage::app('admin')->setUseSessionInUrl(false);
            Mage::getConfig()->init()->loadEventObservers('crontab');
            Mage::app()->addEventArea('crontab');
            Mage::dispatchEvent('default');
        } catch (Exception $e) {
            $status = $e->getMessage();
            $this->logger->error($e->getMessage() . "\n\n" . $e->getTraceAsString());
        }
        $this->logger->info('Cron - Fim');

        $this->getResponse()->setBody($status);
    }

}