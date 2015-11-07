<?php


class Allpago_Installments_Model_Source_Productform
{
    public function toOptionArray()
    {
       return array(
           array('value'=>'1', 'label'=>Mage::helper('installments')->__('Radio')), 
           array('value'=>'0', 'label'=>Mage::helper('installments')->__('Dropdown'))
            
        );
    }
}