<?php


class Allpago_Installments_Model_Source_Maxinstallmenttypes
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'semjuros', 'label'=>Mage::helper('installments')->__('Sem Juros')),
            array('value'=>'comjuros', 'label'=>Mage::helper('installments')->__('Com Juros'))
        );
        
    }
}