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

class Ideasa_Base_Helper_Session extends Mage_Core_Helper_Abstract {

    private $logger;

    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->logger = Ideasa_Base_Logger::getLogger(__CLASS__);
    }

    public function getCurrentOrder() {
        $session = Mage::getSingleton('checkout/session');
        $order = Mage::getModel('sales/order');

        $sessionQuote = $session->getQuote();
        $orderId = null;
        if ($orderId == null || $orderId == "") {
            $orderId = $sessionQuote->getReservedOrderId();
            $lastRealOrderId = $session->getLastRealOrderId();
            if ($lastRealOrderId != null && $lastRealOrderId != "") {
                $orderId = $lastRealOrderId;
            }
        }

        if ($orderId == null || $orderId == "") {
            $order->loadByIncrementId($session->getLastRealOrderId());
        } else {
            $order->loadByAttribute('increment_id', $orderId);
        }

        return $order;
    }

}