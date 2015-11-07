<?php
class Allpago_Installments_Block_Adminhtml_Renderer_Selectmenor extends Mage_Core_Block_Html_Select
{
    protected function _toHtml()
    {
    	$this->addOption('lteq', $this->__('Menor ou igual que'));
    	$this->addOption('lt', $this->__('Menor que'));
    	
    	return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}