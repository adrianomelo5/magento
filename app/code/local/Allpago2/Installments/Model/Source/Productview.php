<?php


class Allpago_Installments_Model_Source_Productview
{
    public function toOptionArray()
    {
       return array(
            array('value'=>'0', 'label'=>Mage::helper('installments')->__('Não')),
            array('value'=>'productlistsimple', 'label'=>Mage::helper('installments')->__('Simples')),
            array('value'=>'productviewtable', 'label'=>Mage::helper('installments')->__('Detalhado'))
        );
    }
}