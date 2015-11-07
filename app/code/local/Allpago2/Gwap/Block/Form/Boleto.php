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
class Allpago_Gwap_Block_Form_Boleto extends Mage_Payment_Block_Form {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('allpago_gwap/form/boleto.phtml');
    }
    
    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getAvailableTypes()
    {
        $_types = Mage::getModel('gwap/source_boleto')->toOptionArray();
        
        $types = array();
        
        $path = 'payment/gwap_boleto/types';
        $availableTypes = explode(',',Mage::getStoreConfig($path) );
        
        foreach ($_types as $data) {
            if (isset($data['label']) && isset($data['value'])) {
                if(in_array($data['value'], $availableTypes))
                    $types[$data['value']] = $data['label'];
                
            }
        }
         
        return $types;
    }
}