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
class Allpago_Gwap_Model_Source_Cctypes_Redecard {

    public function toOptionArray() {
        return array(
            array('value' => 'VISA_R', 'label' => Mage::helper('gwap')->__('Visa')),
            array('value' => 'MASTER_R', 'label' => Mage::helper('gwap')->__('Mastercard')),
            array('value' => 'DINERS_R', 'label' => Mage::helper('gwap')->__('Diners')),            
            array('value' => 'HIPERCARD_R', 'label' => Mage::helper('gwap')->__('Hipercard')),
            array('value' => '', 'label' => 'Não utilizar operadora Redecard'),            
        );
    }

}