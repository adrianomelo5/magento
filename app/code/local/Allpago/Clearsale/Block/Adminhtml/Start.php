<?php

class Allpago_Clearsale_Block_Adminhtml_Start extends Mage_Payment_Block_Form {

    public function _construct() {
        parent::_construct();
        $this->setTemplate('allpago_clearsale/start.phtml');
    }

    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function statusPedido() {
        $order = Mage::registry('current_order');
        if($order){
            return Mage::getModel('gwap/order')->load($order->getId(),'order_id')->getStatus();
        }
        return false;
    }
    
//    public function getLog() {
//        
//        if( $this->getOrder() )
//        return Mage::getModel('allpago_mc/log')->getCollection()->addOrderFilter($this->getOrder()->getId())->setOrder('id', 'DESC');
//    }

}