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
class Allpago_Gwap_Model_Source_Environment {

    public function toOptionArray() 
    {
        return array(
            array('value'=>'LIVE', 'label'=>Mage::helper('gwap')->__('Produção – Live')),
            array('value'=>'INTEGRATOR_TEST', 'label'=>Mage::helper('gwap')->__('Teste – Integrator Test')),
            array('value'=>'CONNECTOR_TEST', 'label'=>Mage::helper('gwap')->__('Teste – Connector Test'))
        );
    }

}