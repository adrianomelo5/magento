<?php
class Allpago_Installments_Block_Adminhtml_Renderer_Selectmaior extends Mage_Core_Block_Html_Select
{
    protected function _toHtml()
    {
    	$this->addOption('gteq', $this->__('Maior ou igual que'));
    	$this->addOption('gt', $this->__('Maior que'));
    	
    	return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}