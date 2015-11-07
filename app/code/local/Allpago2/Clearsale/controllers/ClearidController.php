<?php

class Allpago_Clearsale_ClearidController extends Mage_Core_Controller_Front_Action
{
    
    public function retornoAction(){
                
        $params = $this->getRequest()->getParams();
        
        if(sizeof($params)){
            
            $chave = 'AAA-BBB-CCC';//Mage::getStoreConfig('allpago/clearsale/ws_key');
            $codigo = sha1($params['PedidoID'].'##'.$params['StatusPedido'].'##'.$chave);
            
            if(strtoupper($codigo) == $params['Hash']){

                $clearsale = Mage::getModel('clearsale/orders')->load($params['PedidoID'],'order_id');
                      
                try{
                    
                    if($clearsale->getOrderId()){
                        
                        $log = Mage::getModel('allpago_mc/log');  

                        //Reprovado
                        if(in_array($params['StatusPedido'],array('RPQ','RPP'))) {
                            //Salva log
                            $log->add($clearsale->getOrderId(), 'Clearsale', 'retorno()', Allpago_Clearsale_Model_Orders::STATUS_APPROVED, 'Análise aprovada', $this->getStatusType($params['StatusPedido']));

                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_DENIED);
                            $clearsale->setStatusClearsale($this->getStatusType($params['StatusPedido']));
                            $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $clearsale->save(); 

                            //Cancelar pedido reprovado
                            if(Mage::getStoreConfig('allpago/clearsale/cancelamento')){
                                $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($clearsale->getOrderId(), 'order_id');
                                $gatewayPayment->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
                                $gatewayPayment->setInfo(null);
                                $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                                $gatewayPayment->save();
                                //Cancela o pedido
                                Mage::getModel('sales/order')->load($clearsale->getOrderId())->cancel()->save();
                            }                            
                            
                            //Tela de falha
                            $this->failureRedirect($this->getStatusType($params['StatusPedido']));
                            
                        // Aprovado
                        }elseif($params['StatusPedido'] == 'APQ'){

                            $log->add($clearsale->getOrderId(), 'Clearsale', 'retorno()', Allpago_Clearsale_Model_Orders::STATUS_DENIED, 'Análise reprovada', $this->getStatusType($params['StatusPedido']));
                            //Define retorno na tabela
                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CAPTUREPAYMENT);
                            $clearsale->setStatusClearsale($this->getStatusType($params['StatusPedido']));
                            $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $clearsale->save(); 
 
                            //Tela de sucesso
                            $block = Mage::app()->getLayout()->getMessagesBlock();
                            $block->addSuccess('Transação autorizada com sucesso');

                        }else{
                            Mage::throwException('Status inválido');
                        }          
                    }else{
                        Mage::throwException('Pedido não encontrado');
                    }                    
                    
                } catch (Exception $e) {
                    Mage::log(__METHOD__.' | Erro: '.$e->getMessage(),null,'erro_clearsale_retorno.log');
                    $this->failureRedirect($e->getMessage());
                }
                    
            }
            
        }
        
    }
    
    public function getStatusType($status) {
        
        $arrayStatus = array('APA'=> 'APA (Aprovação Automática)',
                             'APM'=> 'APM (Aprovação Manual)',
                             'PAV'=> 'PAV (Pendente  de  Auto  Validação)',
                             'APQ'=> 'APQ (Aprovado  Por  Questionário)',            
                             'RPM'=> 'RPM (Reprovado Sem Suspeita)',
                             'AMA'=> 'AMA (Análise manual)',
                             'ERR'=> 'ERR (Erro)',
                             'NVO'=> 'NVO (Novo)',
                             'SUS'=> 'SUS (Suspensão Manual)',
                             'CAN'=> 'CAN (Cancelado pelo Cliente)',
                             'FRD'=> 'FRD (Fraude Confirmada)',
                             'RPA'=> 'RPA (Reprovação Automática)',
                             'RPQ'=> 'RPQ (Reprovado por Questionário)',
                             'RPP'=> 'RPP (Reprovação Por Política)');
        
        return array_key_exists((string)$status, $arrayStatus) ? $arrayStatus[(string)$status] : 4;
    }    
     
    public function failureRedirect($errorMsg) {

        $block = Mage::app()->getLayout()->getMessagesBlock();
        $block->addError('Falha no retorno do pedido (' . $errorMsg . ')');
        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('allpago_gwap/checkout/failure'));
        Mage::app()->getResponse()->sendResponse();        

        exit;
    }    
}
 