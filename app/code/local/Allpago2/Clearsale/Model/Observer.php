<?php

class Allpago_Clearsale_Model_Observer {

    const SALES_ORDER_VIEW_INFO_BLOCK = 'Mage_Adminhtml_Block_Sales_Order_View_Messages';    
    
    public function addPrazoEntrega($observer) {
        
        if(!Mage::getStoreConfig('allpago/clearsale/active')
                || Mage::getStoreConfig('allpago/clearsale/produto') == 'start'){
            return false;
        }
        
        $quote = $observer->getQuote();
        if (is_object($quote)) {

            $method = $quote->getShippingAddress()->getShippingMethod();
            $address = $quote->getShippingAddress();
            
            $prazo = "Até 3 dias úteis";
            if ($method) {
               foreach ($address->getAllShippingRates() as $rate) {
                    if ($rate->getCode()==$method
                            && $rate->getCode() != 'freeshipping_freeshipping') {
                        $prazo = $rate['method_description'];
                        break;
                    }
                }
            }   
            if($prazo){
                $quote->setPrazoEntrega($prazo);
            }
        }
    }
    

    public function startCsOrderBlock($observer) {
        if(Mage::getStoreConfig('allpago/clearsale/active') 
                && Mage::getStoreConfig('allpago/clearsale/produto') == 'start'){
            if (self::SALES_ORDER_VIEW_INFO_BLOCK == get_class($observer->getBlock())) {
                $observer->getTransport()->setHtml(
                        $observer->getTransport()->getHtml()
                        . $observer->getBlock()->getLayout()->createBlock('clearsale/adminhtml_start')->toHtml()
                );
            } else {
                $observer->getTransport()->setHtml($observer->getTransport()->getHtml());
            }
        }
    }    
}
