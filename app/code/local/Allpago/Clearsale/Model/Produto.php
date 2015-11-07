<?php

class Allpago_Clearsale_Model_Produto
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'start', 'label'=>'Start'),
            array('value' => 'clearid', 'label'=>Mage::helper('adminhtml')->__('ClearID')),
            array('value' => 'tg', 'label'=>Mage::helper('adminhtml')->__('A-CS, T-CS e TG-CS')),
        );
    }

}
