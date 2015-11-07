<?php

class Allpago_Installments_Block_Productviewsimple extends Allpago_Installments_Block_Abstract
{
	public function _construct()
	{
		$this->setTemplate('allpago_installments/productviewsimple.phtml');
	}
	
	/*
	 * 
	 */
	public function getInstallmentHighest()
	{
		if (!$this->getValue())
		{
			Mage::throwException('A value must be set for Installments to render correctly.');
		}
		return $this->getModel()->setValue($this->getValue())->getInstallmentHighest();
	}
}