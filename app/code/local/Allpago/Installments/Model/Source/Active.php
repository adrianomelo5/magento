<?php


class Allpago_Installments_Model_Source_Active
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('installments')->__('Não')),
            array('value'=>'auto', 'label'=>Mage::helper('installments')->__('Modo Automático')),
            array('value'=>'advance', 'label'=>Mage::helper('installments')->__('Modo Avançado'))
        );
    }
}