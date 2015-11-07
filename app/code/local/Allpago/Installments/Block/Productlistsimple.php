<?php

class Allpago_Installments_Block_Productlistsimple extends Allpago_Installments_Block_Abstract
{
	public function _construct()
	{
		$this->setTemplate('allpago_installments/productlistsimple.phtml');
	}
	
	/*
	 * 
	 */
	public function getInstallmentHighest()
	{
		if (!$this->getValue() || $this->getValue() <= 0 )
		{
                    return $this->getModel(); //Mage::throwException('A value must be set for Installments to render correctly.');
		}
		$installmentModel = $this->getModel();
		$installmentModel->setValue($this->getValue());
		$installmentModel->setMaxParcelasRewrite($this->getMaxParcelasRewrite());
		return $installmentModel->getInstallmentHighest();
	}
}