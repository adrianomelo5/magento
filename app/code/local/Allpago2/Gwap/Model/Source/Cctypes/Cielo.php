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
class Allpago_Gwap_Model_Source_Cctypes_Cielo {

    public function toOptionArray() {
        return array(
            array('value' => 'VISA_C', 'label' => Mage::helper('gwap')->__('Visa')),
            array('value' => 'MASTER_C', 'label' => Mage::helper('gwap')->__('Mastercard')),
            array('value' => 'DINERS_C', 'label' => Mage::helper('gwap')->__('Diners')),
            array('value' => 'DISCOVER_C', 'label' => Mage::helper('gwap')->__('Discover')),
            array('value' => 'ELO_C', 'label' => Mage::helper('gwap')->__('Elo')),
            array('value' => 'AMEX_C', 'label' => Mage::helper('gwap')->__('Amex')),
            array('value' => 'JCB_C', 'label' => Mage::helper('gwap')->__('Jcb')),  
            array('value' => '', 'label' => 'Não utilizar operadora Cielo'),            
        );
    }

}