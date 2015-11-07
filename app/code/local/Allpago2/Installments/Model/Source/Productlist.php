<?php


class Allpago_Installments_Model_Source_Productlist
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('installments')->__('Não')),
            array('value'=>'productlistsimple', 'label'=>Mage::helper('installments')->__('Sim'))
        );
        
    }
}