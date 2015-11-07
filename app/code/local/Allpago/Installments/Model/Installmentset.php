<?php

class Allpago_Installments_Model_Installmentset extends Varien_Object
{
	private $_installmentArray;
	
	public function _construct()
	{
		$this->_installmentArray = array();
	}
	
	public function pushInstallment($capital = 0.0, $mensagem = '', $value = 1, $checked = false, $extraparams = '')
	{
		array_push(	$this->_installmentArray,
					Mage::getModel('installments/installment')
					->setValue($value)
					->setIsChecked($checked)
					->setInstallment(Mage::helper('core')->currency($capital))
					->setInstallmentValue( $capital )
					->setMessage($mensagem)
					->setExtraParams($extraparams)
		);
		return $this;
	}
	
	public function returnIterable()
	{
		return $this->_installmentArray;
	}
	
	public function updateIterable($update)
	{
		return $this->_installmentArray = $update;
	}
}