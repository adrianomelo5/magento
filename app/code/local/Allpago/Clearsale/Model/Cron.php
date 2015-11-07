<?php

class Allpago_Clearsale_Model_Cron extends Mage_Core_Model_Abstract {
    

    public function getOrdersStatus(){
        if(Mage::getStoreConfig('allpago/clearsale/active')){
            if(Mage::getStoreConfig('allpago/clearsale/produto') == 'tg'){
                Mage::getModel('clearsale/clearsale')->getOrdersStatus();
            }elseif(Mage::getStoreConfig('allpago/clearsale/produto') == 'clearid'){
                Mage::getModel('clearsale/clearid')->CheckOrderStatus();
            }
        }
    }    

    public function sendOrders(){
        if(Mage::getStoreConfig('allpago/clearsale/active')){
            if(Mage::getStoreConfig('allpago/clearsale/produto') == 'tg'){
                Mage::getModel('clearsale/clearsale')->sendOrders();
            }elseif(Mage::getStoreConfig('allpago/clearsale/produto') == 'clearid'){
                if(Mage::getStoreConfig('allpago/clearsale/clearid_questionario')){
                    Mage::getModel('clearsale/clearid')->SubmitInfoID();
                }else{
                    Mage::getModel('clearsale/clearid')->SubmitInfoSimple();
                }
            }
        }
    }    
    
}