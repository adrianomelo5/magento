<?php

class Allpago_Gwap_CheckoutController extends Mage_Core_Controller_Front_Action
{
    public function failureAction(){
        
        if(Mage::getSingleton('core/session')->getGwapFailure()){
            Mage::getSingleton('core/session')->setGwapFailure();            
            $this->loadLayout();
            $this->renderLayout();
        }else{
            $this->_redirect('checkout/cart');
        }
            
    } 
  
}
 