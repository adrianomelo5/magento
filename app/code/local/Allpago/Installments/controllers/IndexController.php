<?php

class Allpago_Installments_IndexController extends Mage_Core_Controller_Front_Action{
    
    public function ajaxAction(){
         
        $customValue = $this->getRequest()->getParam('value');
        $sortNumber = $this->getRequest()->getParam('sort',false);
        
        if(!$customValue){
            $this->getResponse()->setBody('Erro ao retornar valores de parcelamento: valor não foi informado.');
            return;
        }
        
        $customValue = str_replace(',','.',str_replace('.','',str_replace('R$ ','',$customValue)));
        
        $this->getResponse()->setBody($this->getLayout()->createBlock('installments/formcustom')
                               ->setPaymentInstallmentCode('parcelas')
                               ->setPaymentCode('gwap_2cc')
                               ->setSortNumber($sortNumber)
                               ->setCustomValue($customValue)
                               ->toHtml());
    }
    
}