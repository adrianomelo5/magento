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
class Allpago_Gwap_Model_Source_Channel_Cc {

    public function toOptionArray() {
        return array(
            array('value' => 'cielo', 'label' => Mage::helper('gwap')->__('Cielo')),
            array('value' => 'rcard', 'label' => Mage::helper('gwap')->__('Rede Card')),
            array('value' => 'firstdata', 'label' => Mage::helper('gwap')->__('FirstData'))
        );
    }

}