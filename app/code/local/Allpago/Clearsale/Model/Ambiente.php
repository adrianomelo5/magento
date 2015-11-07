<?php

class Allpago_Clearsale_Model_Ambiente
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'homologacao', 'label'=>Mage::helper('adminhtml')->__('Homologação')),
            array('value' => 'producao', 'label'=>Mage::helper('adminhtml')->__('Produção')),
        );
    }

}
