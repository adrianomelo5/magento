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
class Allpago_Gwap_Block_Info_Boleto extends Mage_Payment_Block_Info {

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
        if ($this->getInfo()->getGwapBoletoType()) {
            $data[Mage::helper('payment')->__('Banco')] = $this->getInfo()->getGwapBoletoType();
        }
        if( $this->getInfo()->getOrder() && $this->getInfo()->getOrder()->hasData() ){
            $order = $this->getInfo()->getOrder();
            $orderId = $order->getId();
            $customerId = $order->getCustomerId();
            if ($this->getInfo()->getOrder()->getId()) {
                $gwapItem = Mage::getModel('gwap/order')->load($this->getInfo()->getOrder()->getId(), 'order_id');
                if( $gwapItem->getStatus() == Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT || $gwapItem->getStatus() == Allpago_Gwap_Model_Order::STATUS_CREATED ){
                    $store = Mage::getModel('core/store')->load($order->getStoreId());
                    /* @var $store Mage_Core_Model_Store */
                    $boletoUrl = $store->getUrl('allpago_gwap/imprimir/boleto', array('id'=>$orderId, 'ci'=>$customerId));
                    $transport->addData(array(
                       Mage::helper('payment')->__('Reimprimir Boleto') => "<a href=\"{$boletoUrl}\" target=\"_blank\">Clique aqui para abrir o boleto</a>")
                       );

                }
            }
        }
        
        return $transport->setData(array_merge($data, $transport->getData()));
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