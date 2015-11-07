<?php
class Correio_Shipping_Model_Testemethod  
{
    public function toOptionArray()
    {
        return array(
                
            array('value'=>1, 'label'=>Mage::helper('adminhtml')->__('Teste 1')),
            array('value'=>0, 'label'=>Mage::helper('adminhtml')->__('Teste 2')),
        );
    }
}