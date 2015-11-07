<?php

class Allpago_Gwap_Model_Observer {

    public function addBoletoLink($observer) {
        $orderId = current($observer->getOrderIds());
        $mGwap = Mage::getModel('gwap/order')->load($orderId, 'order_id');
        if ($mGwap->getType() != 'boleto') {
            return $this;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        $storage = Mage::getSingleton('checkout/session');

        if ($storage) {
            $block = Mage::app()->getLayout()->getMessagesBlock();

            $url = Mage::helper('gwap')->getBoletoUrl($order->getIncrementId());

            $linkMessage = Mage::helper('gwap')->__('Clique aqui');
            $block->addSuccess(
                    sprintf(Mage::helper('gwap')->__('%s para imprimir seu boleto.'), '<span class="retorno"><a href="' . $url . '" target="_blank" class="imprimir_boleto">' . $linkMessage . '</a></span>')
            );
        }
    }

    /**
     * cancela verificacoes de boletos e pedidos de acordo com o vencimento
     * 
     * @return Allpago_Gwap_Model_Observer 
     */
    public function cancelBoleto() {
        $cancelamento = Mage::getStoreConfig('payment/gwap_boleto/cancelamento');
        if (is_numeric($cancelamento) && $cancelamento > 0) {
            $cancelamento++;
            $due_date = Mage::getModel('core/date')->timestamp('-' . $cancelamento . ' days');
        } else {
            $due_date = Mage::getModel('core/date')->timestamp('-2 days');
        }

        $mGwap = Mage::getModel('gwap/order')->getCollection()
                ->addExpireFilter($due_date)
                ->addTypeFilter('boleto')
                ->addStatusFilterCustom(Allpago_Gwap_Model_Order::STATUS_CREATED, Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT);

        $log = Mage::getModel('allpago_mc/log');

        if ($mGwap->count()) {
            foreach ($mGwap as $mGwapitem) {

                $mGwapitem->setStatus('canceled');
                $mGwapitem->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $mGwapitem->save();

                $can_cancel = Mage::getStoreConfig('payment/gwap_boleto/cancelar_expirado');

                if ($can_cancel) {

                    $log->add($mGwapitem->getOrderId(), '+ Conversao', 'cancelBoleto()', 'error', 'Pedido expirado');

                    $order = Mage::getModel('sales/order')->load($mGwapitem->getOrderId());
                    /* var $order Mage_Sales_Model_Order */
                    $order->cancel();
                    $order->save();
                }
            }
        }
        return $this;
    }

    public function boletoDiscount($observer) { 
        $shoppingCartPriceRule = Mage::getModel('salesrule/rule')->getCollection();
        if (Mage::getStoreConfig('payment/gwap_boleto/active') && Mage::getStoreConfig('payment/gwap_boleto/desconto') > 0) {
            $flag = false;
            $discount = Mage::getStoreConfig('payment/gwap_boleto/desconto');
            $labels[0] = Mage::getStoreConfig('payment/gwap_boleto/texto_desconto');
            
            foreach ($shoppingCartPriceRule as $rule) {
                if ($rule->getData('name') == 'Boleto Allpago') {
                    $flag = true;

                    $shoppingCartPriceRule = Mage::getModel('salesrule/rule');
                    $shoppingCartPriceRule
                            ->setRuleId($rule->getId())
                            ->setName($rule->getData('name'))
                            ->setDescription('')
                            ->setIsActive(1)
                            ->setWebsiteIds(array(1))
                            ->setCustomerGroupIds(array(0, 1, 2, 3))
                            ->setFromDate('')
                            ->setToDate('')
                            ->setSortOrder('')
                            ->setSimpleAction(Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION)
                            ->setDiscountAmount($discount)
                            ->setStopRulesProcessing(0)
                            ->setStoreLabels($labels);

                    $conditions = array(
                        "1" => array(
                            "type" => "salesrule/rule_condition_combine",
                            "aggregator" => "all",
                            "value" => "1",
                            "new_child" => null
                        ),
                        "1--1" => array(
                            "type" => "salesrule/rule_condition_address",
                            "attribute" => "payment_method",
                            "operator" => "==",
                            "value" => "gwap_boleto"
                        )
                    );

                    try {
                        $shoppingCartPriceRule->setData("conditions", $conditions);
                        $shoppingCartPriceRule->loadPost($shoppingCartPriceRule->getData());
                        $shoppingCartPriceRule->save();
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'erro_gwap_boleto_desconto.log');
                        Mage::getSingleton('core/session')->addError(Mage::helper('catalog')->__($e->getMessage()));
                        return;
                    }
                }
            }
            if (!$flag) {
                $name = "Boleto Allpago";

                $shoppingCartPriceRule = Mage::getModel('salesrule/rule');
                $shoppingCartPriceRule
                        ->setName($name)
                        ->setDescription('')
                        ->setIsActive(1)
                        ->setWebsiteIds(array(1))
                        ->setCustomerGroupIds(array(0, 1, 2, 3))
                        ->setFromDate('')
                        ->setToDate('')
                        ->setSortOrder('')
                        ->setSimpleAction(Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION)
                        ->setDiscountAmount($discount)
                        ->setStopRulesProcessing(0)
                        ->setStoreLabels($labels);

                $conditions = array(
                    "1" => array(
                        "type" => "salesrule/rule_condition_combine",
                        "aggregator" => "all",
                        "value" => "1",
                        "new_child" => null
                    ),
                    "1--1" => array(
                        "type" => "salesrule/rule_condition_address",
                        "attribute" => "payment_method",
                        "operator" => "==",
                        "value" => "gwap_boleto"
                    )
                );

                try {
                    $shoppingCartPriceRule->setData("conditions", $conditions);
                    $shoppingCartPriceRule->loadPost($shoppingCartPriceRule->getData());
                    $shoppingCartPriceRule->save();
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, 'erro_gwap_boleto_desconto.log');
                    Mage::getSingleton('core/session')->addError(Mage::helper('catalog')->__($e->getMessage()));
                    return;
                }
            }
        } else {
            foreach ($shoppingCartPriceRule as $rule) {
                if ($rule->getData('name') == 'Boleto Allpago') {
                    Mage::getModel('salesrule/rule')->load($rule->getId())->delete();
                }
            }
        }
    }

    public function applyAllpagoCcDiscount($observer){   
        
        $params = Mage::app()->getRequest()->getParams();
        $installment = 1;
        
        if(isset($params['payment']['gwap_cc_parcelas'])){
            $installment = $params['payment']['gwap_cc_parcelas'];
        }elseif(isset($params['payment']['gwap_oneclick_parcelas'])){
            $installment = $params['payment']['gwap_oneclick_parcelas'];
        }elseif(isset($params['payment']['gwap_2cc_parcelas']) 
                && isset($params['payment']['gwap_2cc_parcelas2'])){
            if($params['payment']['gwap_2cc_parcelas']<$params['payment']['gwap_2cc_parcelas2']){
                $installment = $params['payment']['gwap_2cc_parcelas'];
            }else{
                $installment = $params['payment']['gwap_2cc_parcelas2'];
            }
        }
        
        if($installment){
            $quote = $observer->getEvent()->getQuote();
            $quote->setInstallments($installment);
            foreach ($quote->getAllAddresses() as $address) {
                $address->setInstallments($installment);
            }                                        
        }

    }     
    
    public function addRvOrRfButton($observer){    
        $block = $observer->getEvent()->getBlock();
        // Order View Page button
        if(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_View'
            && $block->getRequest()->getControllerName() == 'sales_order'){
            $block->addButton('refund', array(
                'label'     => Mage::helper('sales')->__('Cancelar/Estornar Allpago'),
                //'onclick' => 'javascript:openMyPopup()',
                'onclick'   => 'setLocation(\'' . $block->getUrl('gwap/adminhtml_order/removecredit') . '\')',
                'class'     => 'form-button',
            ));
        }        
    }	


    public function setPagamento($observer){
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        if(!$order->getTipoPagamento()){
            if($payment->getMethod() == 'gwap_oneclick' 
                    && $ccLast4 = $payment->getAdditionalInformation('GwapOneclickSelected')){

                $oneclick = Mage::getModel('gwap/oneclick')->getCollection()
                            ->addFieldToFilter('customer_id',$order->getCustomerId())
                            ->addFieldToFilter('cc_last4',$ccLast4);
                
                $order->setTipoPagamento('cc_'.strtolower($oneclick->getFirstItem()->getType()));
                Mage::log($payment->getMethod(),null,'setPagamento.log');
                
            }elseif($payment->getMethod() == 'gwap_cc' && $payment->getCcType()){
                $order->setTipoPagamento('cc_'.strtolower($payment->getCcType()));
            }elseif($payment->getMethod() == 'gwap_boleto' && $payment->getGwapBoletoType()){
                $order->setTipoPagamento('boleto_'.strtolower($payment->getGwapBoletoType()));        
            }
        }
    }     

}
