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

class Ideasa_Base_BoletoController extends Mage_Core_Controller_Front_Action {

    /**
     * Instantiate config
     */
    protected function _construct() {
        parent::_construct();
    }

    public function viewAction() {

        $orderId = $this->getRequest()->getParam('order_id');

        if (null === $orderId) {
            $this->_redirect('/index');
        }

        $order = Mage::getSingleton('sales/order')->load(base64_decode($orderId));

        if (is_null($order->getId())) {
            $this->_redirect('/index');
        }
        $boleto = Mage::getModel('ipgcore/boleto')->view($order->getRealOrderId());
        $pagamento = Mage::getModel('ipgcore/pagamento_pagamento');
        $pagamento->loadByAttribute('order_id', $order->getRealOrderId());
        $meioPagamento = Ipagare_IpgCore_PaymentType::valueOf($pagamento->getMeioPagamento());
        if ($meioPagamento->isBradescoBoletoOnline() || $meioPagamento->isBbBoletoOnline()) {
            $boleto = html_entity_decode($boleto);
        }

        $this->getResponse()->setBody($boleto);
    }

}