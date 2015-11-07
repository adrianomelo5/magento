<?php

class Allpago_Gwap_ImprimirController extends Mage_Core_Controller_Front_Action{
    
    public function boletoAction(){
         
        $order_id = $this->getRequest()->getParam('id');
        $customer_id = $this->getRequest()->getParam('ci');
        
        $mOrder = Mage::getModel('sales/order')->load( $order_id );
        /*@var $mOrder Mage_Sales_Model_Order*/
        
        if( $mOrder->hasData() ){
            if( $customer_id != $mOrder->getCustomerId() ){
                $this->_redirect('no-route');            
                return;
            }
            
            if( $mOrder->getPayment()->getMethod() == 'gwap_boleto'){
                $gwapItem = Mage::getModel('gwap/order')->load( $order_id, 'order_id' );
                if( $gwapItem->hasData() && $gwapItem->getType() == 'boleto' ){
                    
                    if($gwapItem->getStatus() == Allpago_Gwap_Model_Order::STATUS_CREATED){
                        $itemId = $gwapItem->getId();
                        $gwapItem->clearInstance();      
                        // faz primeira tentativa de gerar o boleto
                        try{
                            //create new objects
                            $log = Mage::getModel('allpago_mc/log');                            
                            
                            $mOrder->getPayment()->getMethodInstance()->authorize($mOrder->getPayment(), $mOrder->getGrandTotal());
                            $gwapItem = Mage::getModel('gwap/order')->load( $itemId , 'order_id' );                                                        
                            $gwapItem->setStatus(Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT);
                            $gwapItem->setErrorCode(null);
                            $gwapItem->setErrorMessage(null);
                            $gwapItem->setTries(0);
                            $gwapItem->setAbandoned(0);
                            $gwapItem->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $gwapItem->save();
                            
                            $log->add($gwapItem->getOrderId(), 'Payment', 'authorize()', Allpago_Gwap_Model_Order::STATUS_AUTHORIZED, 'Boleto gerado (pendente de captura)');
                            
                            //redirectiona para url do boleto
                            if( $gwapItem->getStatus() == Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT ){
                                $this->_redirectUrl($gwapItem->getInfo());
                                return ;
                            }
                        }catch (Exception $e) {
                            //Salva log
                            
                            $log->add($gwapItem->getOrderId(), '+ Conversao', 'authorize()', Allpago_Gwap_Model_Order::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());
                            $gwapItem = Mage::getModel('gwap/order')->load( $itemId );
                            $gwapItem->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $gwapItem->save();
                            
                            $url = Mage::getUrl('sales/order/view', array('order_id'=>$order_id));
                            $linkMessage = Mage::helper('gwap')->__('Clique aqui');
                                
                            $this->getResponse()->setBody( sprintf( Mage::helper('gwap')->__('Não foi possível gerar seu boleto no momento. Você pode reimprimir acessando o detalhe de seu pedido. %s.'), '<a href="'.$url.'" target="_blank" class="imprimir_boleto">'.$linkMessage.'</a>' ) );
                            return ;    
                        }
                    }elseif($gwapItem->getStatus() == Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT){
                        //redirectiona para url do boleto
                        $this->_redirectUrl($gwapItem->getInfo());
                        return ;
                    }
                    
                }
            }
        }    
        
        $this->_redirect('no-route');
    }
    
}