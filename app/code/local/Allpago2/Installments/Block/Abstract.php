<?php

class Allpago_Installments_Block_Abstract extends Mage_Core_Block_Template
{
	protected $_installmentModel;
	protected $_processedInstallments;
	
        
        
	/*
	 * 
	 */
	public function getModel()
	{
		if (!$this->_installmentModel){
                    $this->_installmentModel =   Mage::helper('installments')->getInstallmentModel();
                    if($this->getMaxInstallment()){
                        $this->_installmentModel->setMaxInstallment($this->getMaxInstallment());
                    }
                    if($this->getHasInstallment()){
                        $this->_installmentModel->setHasInstallment(true); 
                        $this->_installmentModel->setTaxaJuros($this->getTaxaJuros());
                    }
                    if($this->getCustomValue()){
                        $this->_installmentModel->setValue($this->getCustomValue());
                    }
                   
                }
		return $this->_installmentModel;
	}	
	
	/*
	 * 
	 */
	public function isActive()
	{
		return $this->getModel()->isActive();
	}
	
	
	/*
	 * 
	 */
	protected function _getInstallments($getFromSession = false)
	{
		if (!$this->_processedInstallments)
		{
			$_model = $this->getModel();

			if ($getFromSession)
			{
				$_model->getValue();
			}
			
			$this->_processedInstallments = $_model->getInstallmentSequence();
		}
		
		return $this->_processedInstallments;
	}
}