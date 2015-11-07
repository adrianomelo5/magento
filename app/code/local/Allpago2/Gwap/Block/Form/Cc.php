<?php

/**
 * Allpago - Gwap Payment Module
 *
 * @title      Magento -> Custom Payment Module for Gwap
 * @category   Payment Gateway
 * @package    Allpago_Gwap
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Block_Form_Cc extends Mage_Payment_Block_Form_Cc {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('allpago_gwap/form/cc.phtml');
    }
    
    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        //Listando apenas cielo pois já contem todas as bandeiras
        $_typesC = Mage::getModel('gwap/source_cctypes_cielo')->toOptionArray();
        $_typesR = Mage::getModel('gwap/source_cctypes_redecard')->toOptionArray();
        $_typesF = Mage::getModel('gwap/source_cctypes_firstdata')->toOptionArray();
        $_types = array_merge($_typesC,$_typesR,$_typesF);
        
        $types = array();
        foreach ($_types as $data) {
            if (isset($data['label']) && isset($data['value']) && $data['value']!='') {
                $types[substr_replace($data['value'],'',-2)] = $data['label'];
            }
        }
        
        if ($method = $this->getMethod()) {
            //Armazena os cartões habilitados nas duas operadoras
            $availableTypes = $method->getConfigData('cctypes_rcard');
            $availableTypes .= ','.$method->getConfigData('cctypes_cielo');
            $availableTypes .= ','.$method->getConfigData('cctypes_firstdata');
            
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                $allTypes = array();
                // Remove 2 ultimos caracteres do cartão
                foreach($availableTypes as $substype){
                    $allTypes[] = substr_replace($substype,'',-2);
                }
                //Compara com cartões disponíveis
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $allTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }

        return $types;
    }

    public function getParcelaMaxima(){
        $_product = null;
        $parcela_maxima = 0;
        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        foreach($items as $item){

            $_product = Mage::getModel('catalog/product')->load($item->getProductId());                
            
            if ($parcela_maxima < $_product->getParcelaMaxima()){
                $parcela_maxima = $_product->getParcelaMaxima();
            }
          
        }
        return $parcela_maxima;
    }     
    
}