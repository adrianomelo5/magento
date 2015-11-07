<?php

class Allpago_Gwap_Model_Observer {

    /**
     * Set forced canCreditmemo flag
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Payment_Model_Observer
     */
    public function salesOrderSave($observer) {
        
        $orderId = current($observer->getOrderIds());
        $order = Mage::getModel('sales/order')->load($orderId);

        $payment = $order->getPayment();

        if (!is_object($payment) || !in_array($payment->getMethod(), array('gwap_cc', 'gwap_oneclick', 'gwap_boleto','gwap_2cc'))) {
            return $this;
        }

        $data = $this->getGwapPaymentData($payment);
        
        $mGwap = Mage::getModel('gwap/order');
        $mGwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CREATED);
        $mGwap->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        // Não salvar dados do CC
        if (!Mage::getStoreConfig('payment/gwap_cc/tipo_autorizacao') && $payment->getMethod() != "gwap_boleto") {
            $mGwap->setInfo(Mage::helper('core')->encrypt(serialize($data->toArray())));
        }
        $mGwap->setType(Mage::getStoreConfig('payment/' . $payment->getMethod() . '/mc_type'));
        $mGwap->setCcType($data->getCcType());
        if($payment->getMethod() == 'gwap_2cc'){
            $mGwap->setCcType2($data->getCcType2());
        }
        $mGwap->setOrderId($order->getId());
        $mGwap->save();

        if (Mage::getStoreConfig('payment/gwap_cc/tipo_autorizacao')) {
            if ($payment->getMethod() != 'gwap_boleto') {
                $result = $order->getPayment()->getMethodInstance()->authorizeNow($order,$data->toArray());
                if($result && Mage::getStoreConfig('allpago/clearsale/active')
                        && Mage::getStoreConfig('allpago/clearsale/produto') == 'clearid'
                            && Mage::getStoreConfig('allpago/clearsale/clearid_questionario')){
                    Mage::getModel('clearsale/clearid')->SubmitInfo($order);
                }
            }
        }

        if ($payment->getMethod() == "gwap_boleto") {
            $mGwap->setInfo(Mage::helper('core')->encrypt(serialize($data->toArray())));
            $mGwap->save();
            $log = Mage::getModel('allpago_mc/log');
            try{
                $order->getPayment()->getMethodInstance()->authorize($order->getPayment(), $order->getGrandTotal());
                $gwapNovo = Mage::getModel('gwap/order')->load( $order->getId(), 'order_id'); 
                $gwapNovo->setStatus(Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT);
                $gwapNovo->setErrorCode(null);
                $gwapNovo->setErrorMessage(null);
                $gwapNovo->setTries(0);
                $gwapNovo->setAbandoned(0);
                $gwapNovo->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $gwapNovo->save();              
                $log->add($gwapNovo->getOrderId(), 'Payment', 'authorize()', Allpago_Gwap_Model_Order::STATUS_AUTHORIZED, 'Boleto gerado');
            }catch (Exception $e) {
                //Salva log
                $log->add($mGwap->getOrderId(), '+ Conversao', 'authorize()', Indexa_Gwap_Model_Order::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());
                $mGwap->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $mGwap->save();
                //$url = Mage::getUrl('sales/order/view', array('order_id'=>$order->getId()));
                //$linkMessage = Mage::helper('gwap')->__('Clique aqui');
                //$this->getResponse()->setBody( sprintf( Mage::helper('gwap')->__('Não foi possível gerar seu boleto no momento. Você pode reimprimir acessando o detalhe de seu pedido. %s.'), '<a href="'.$url.'" target="_blank" class="imprimir_boleto">'.$linkMessage.'</a>' ) );
                return $this;    
            }
        }
        return $this;
    }

    
    public function getGwapPaymentData($payment){    
        
        $data = new Varien_Object();
        
        if ($payment->getCcType())
            $data->setCcType($payment->getCcType());
        if ($payment->getCcOwner())
            $data->setCcOwner($payment->getCcOwner());
        if ($payment->getCcLast4())
            $data->setCcLast4($payment->getCcLast4());
        if(Mage::getModel('core/session')->getGwapCcCcNumber())    
            $data->setCcNumber(Mage::getModel('core/session')->getGwapCcCcNumber()); 
        if (Mage::getModel('core/session')->getGwapCcId())
            $data->setCcCid(Mage::getModel('core/session')->getGwapCcId());            
        if ($payment->getCcParcelas())
            $data->setCcParcelas($payment->getCcParcelas());
        if ($payment->getCcExpMonth())
            $data->setCcExpMonth($payment->getCcExpMonth());
        if ($payment->getCcExpYear())
            $data->setCcExpYear($payment->getCcExpYear());
        if ($payment->getAdditionalInformation('GwapBoletoType'))
            $data->setGwapBoletoType($payment->getAdditionalInformation('GwapBoletoType'));
        if ($payment->getAdditionalInformation('GwapCheckOneclick'))
            $data->setGwapCcCheckOneclick($payment->getAdditionalInformation('GwapCheckOneclick'));
        if ($payment->getAdditionalInformation('GwapOneclickSelected'))
            $data->setGwapOneclickSelected($payment->getAdditionalInformation('GwapOneclickSelected'));        
        if ($payment->getAdditionalInformation('GwapSessionId'))
            $data->setGwapSessionId($payment->getAdditionalInformation('GwapSessionId'));
        
        Mage::getModel('core/session')->setGwapCcId();
        Mage::getModel('core/session')->setGwapCcCcNumber();        
        
        if($payment->getMethod() == 'gwap_2cc'){
            
            $data->setCcAmount($payment->getAdditionalInformation('gwapCcAmount'));
            $data->setCcType2($payment->getAdditionalInformation('gwapCcType2'));
            $data->setCcOwner2($payment->getAdditionalInformation('gwapCcOwner2'));
            $data->setCcLast4_2($payment->getAdditionalInformation('gwapCcLast4_2'));
            $data->setCcNumber2($payment->getAdditionalInformation('gwapCcNumber2'));
            $data->setCcParcelas2($payment->getAdditionalInformation('gwapCcParcelas2'));
            $data->setCcCid2($payment->getAdditionalInformation('gwapCcCid2'));                
            $data->setCcExpMonth2($payment->getAdditionalInformation('gwapCcExpMonth2'));                
            $data->setCcExpYear2($payment->getAdditionalInformation('gwapCcExpYear2'));                                

            $payment->setAdditionalInformation('gwapCcNumber2','');
            $payment->setAdditionalInformation('gwapCcCid2','');
            
        }
        
        if($payment->getMethod() == 'gwap_oneclick'){
            
            $data->setCcType($payment->getAdditionalInformation('oneclickType'));
            
        }        
        
        return $data;
    }
    
    public function addBoletoLink($observer) {
        $orderId = current($observer->getOrderIds());
        $mGwap = Mage::getModel('gwap/order')->load($orderId, 'order_id');
        if ($mGwap->getType() != 'boleto') {
            return $this;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        //$customerId = $order->getCustomerId();

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
        $installment = 0;
        
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
            foreach ($quote->getAllAddresses() as $address) {
                $address->setInstallments($installment);
                Mage::log($installment,null,'parcela.log');
            }                                        
        }

    }     
    
    public function clearInfoInstallments($observer){
        $checkout = Mage::getSingleton('checkout/session');
        $checkout->setJuros(0);
        $checkout->setBaseTotal(0);
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
