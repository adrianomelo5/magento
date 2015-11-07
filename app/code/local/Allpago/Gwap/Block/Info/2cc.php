<?php

/**
 * Allpago - AllPago Payment Module
 *
 * @title      Magento -> Custom Payment Module for AllPago
 * @category   Payment Gateway
 * @package    Allpago_AllPago
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Block_Info_2cc extends Mage_Payment_Block_Info {

    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();
        
        if ($this->getInfo()->getCcType()) {
            $data[Mage::helper('payment')->__('Credit Card Type')] = $this->getInfo()->getCcType().' | '.$this->getInfo()->getAdditionalInformation('gwapCcType2');
        }
        if ($this->getInfo()->getCcLast4()) {
            $data[Mage::helper('payment')->__('Credit Card Number')] = sprintf('xxxx-%s', $this->getInfo()->getCcLast4()).' | '.sprintf('xxxx-%s', $this->getInfo()->getAdditionalInformation('gwapCcLast4_2'));
        }
        if ($this->getInfo()->getCcParcelas()) {
            $data[Mage::helper('payment')->__('Parcelamento')] = $this->getInfo()->getCcParcelas().'x'.' | '.$this->getInfo()->getAdditionalInformation('gwapCcParcelas2').'x';
        }
        
        if( $this->getInfo()->getOrder() && $this->getInfo()->getOrder()->hasData() ){
            
            $orderID = $this->getInfo()->getOrder()->getId();
            if($orderID){
                $gwap = Mage::getModel('gwap/order')->load($orderID, 'order_id');
                if($gwap->hasData() && $gwap->getCaptureResult()){
                    
                    $TID = array();
                    $TID = unserialize($gwap->getCaptureResult());
                    
                    if(isset($TID['ConnectorTxID1'])){
                         $data['ConnectorTxID1'] = $TID['ConnectorTxID1'];
                    }
                    if(isset($TID['ConnectorTxID2'])){
                        $data['ConnectorTxID2'] = $TID['ConnectorTxID2'];
                    }        
                    if(isset($TID['ConnectorTxID3'])){
                        $data['ConnectorTxID3'] = $TID['ConnectorTxID3'];
                    }
                    if(isset($TID['LR'])){
                        $data['LR'] = $TID['LR'];
                    }
                    if(isset($TID['NSU'])){
                        $data['NSU'] = $TID['NSU'];
                    }                     
                   
                }
            }
        }
        
        return $transport->setData(array_merge($data, $transport->getData()));
    }
    
}