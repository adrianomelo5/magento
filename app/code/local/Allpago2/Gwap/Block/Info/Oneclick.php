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
class Allpago_Gwap_Block_Info_Oneclick extends Mage_Payment_Block_Info {

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
        
        if( $this->getInfo()->getOrder() && $this->getInfo()->getOrder()->hasData() ){
            
            $ccLast4 = $this->getInfo()->getAdditionalInformation('GwapOneclickSelected');
            $oneclick = Mage::getModel('gwap/oneclick')->getCollection()
                        ->addFieldToFilter('customer_id',$this->getInfo()->getOrder()->getCustomerId())
                        ->addFieldToFilter('cc_last4',$ccLast4);

            $oneclick = $oneclick->getFirstItem();

            $data = array();
            $data[Mage::helper('payment')->__('Credit Card Type')] = $oneclick->getType();
            $data[Mage::helper('payment')->__('Credit Card Number')] = 'xxxx-'.$oneclick->getCcLast4();
            if ($this->getInfo()->getCcParcelas()) {
                $data[Mage::helper('payment')->__('Parcelamento')] = $this->getInfo()->getCcParcelas().'x';
            } 
            
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
            
            return $transport->setData(array_merge($data, $transport->getData()));        
        }else{
            $transport = new Varien_Object();
            $transport = parent::_prepareSpecificInformation($transport);
            return $transport;
        }
    }
    
    
    public function getValueAsArray($value, $escapeHtml = false)
    {
    	$escapeHtml = false;
    	
        if (empty($value)) {
            return array();
        }
        if (!is_array($value)) {
            $value = array($value);
        }
        
        return $value;
    }
}