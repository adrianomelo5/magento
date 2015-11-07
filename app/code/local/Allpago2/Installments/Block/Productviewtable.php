<?php

class Allpago_Installments_Block_Productviewtable extends Allpago_Installments_Block_Abstract
{
	public function _construct()
	{
		$this->setTemplate('allpago_installments/productviewtable.phtml');
	}
	
	public function getInstallments()
	{
            	$this->getModel()->setValue($this->getValue());
                return $this->_getInstallments()->returnIterable();
	}
}