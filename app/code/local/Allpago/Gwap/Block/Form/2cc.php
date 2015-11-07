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
class Allpago_Gwap_Block_Form_2cc extends Mage_Payment_Block_Form {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('allpago_gwap/form/2cc.phtml');
    }  
    
    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
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
        
        //Armazena os cartões habilitados nas duas operadoras
        $availableTypes = Mage::getStoreConfig('payment/gwap_cc/cctypes_rcard');
        $availableTypes .= ','.Mage::getStoreConfig('payment/gwap_cc/cctypes_cielo');
        $availableTypes .= ','.Mage::getStoreConfig('payment/gwap_cc/cctypes_firstdata');

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
        return $types;
    }   
    
    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }    
    
    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }    
        
}