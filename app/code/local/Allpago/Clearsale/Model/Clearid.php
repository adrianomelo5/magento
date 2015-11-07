<?php

class Allpago_Clearsale_Model_Clearid extends Allpago_Clearsale_Model_Abstractid {

    
    private $PedidoID;
    private $SessionID;
    private $Data;
    private $Email;
    private $ValorFrete;
    private $PrazoEntrega;
    private $ValorTotalItens;
    private $ValorTotalPedido;
    private $QtdItens;
    private $QtdParcelas;
    private $IP;
    private $Obs;
    
    private $Itens;
    private $DadosCobranca;
    private $DadosEntrega;
    private $Pagamentos;
    
    private $qtd_tentativas = 3;
    
    public function limparParametros(){
        $this->PedidoID = NULL;
        $this->SessionID = NULL;
        $this->Data = NULL;
        $this->Email = NULL;
        $this->ValorFrete = NULL;
        $this->PrazoEntrega = NULL;
        $this->ValorTotalItens = NULL;
        $this->ValorTotalPedido= NULL;
        $this->QtdItens = NULL;
        $this->QtdParcelas = NULL;
        $this->IP = NULL;
        $this->Obs = NULL;
                
        $this->Itens = array();
        $this->DadosCobranca = array();
        $this->DadosEntrega = array();
        $this->Pagamentos = array();
    }
    
    public function __construct(){
        $this->qtd_tentativas = (Mage::getStoreConfig('allpago/clearsale/qtd_tentativas')) ? Mage::getStoreConfig('allpago/clearsale/qtd_tentativas') : $this->qtd_tentativas;
    }
    
    public function criarParametros($order) {

        //Dados do cliente
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId()); 

        try{
            $customerCpfs = explode(",", Mage::getStoreConfig('allpago/clearsale/campo_cpf'));
            $cpf = null;
            foreach ($customerCpfs as $customerCpf) {
                $metodo = 'get' . ucfirst($customerCpf);
                if (!$cpf && $customer->$metodo()) {
                    $cpf = (string) preg_replace('/[^0-9]/', '', $customer->$metodo());
                }
            }        

            // Sigla do Estado
            $directoryRegion = Mage::getResourceModel('directory/region_collection');
            $directoryRegion->getSelect()->reset()->from(array('main_table' => $directoryRegion->getMainTable()), 'code');
            $directoryRegion->addFieldToFilter('country_id','BR')->addFieldToFilter('region_id',$order->getBillingAddress()->getRegionId());
            $billingRegion = $directoryRegion->getResource()->getReadConnection()->fetchOne($directoryRegion->getSelect()); 

            // Verificação do endereço de entrega
            if(is_object($order->getBillingAddress())){
                $billingCustomerId = $order->getBillingAddress()->getCustomerId();
                $billingCity = $order->getBillingAddress()->getCity();
                $billingCustomerName = $order->getShippingAddress()->getName();
                $billingEmail = $order->getBillingAddress()->getEmail() ? $order->getBillingAddress()->getEmail() : $order->getCustomerEmail();   
                $billingTelephone = str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getTelephone()));
                $billingCellphone = str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getCellPhone()));
                $billingPostcode = str_replace(' ', '', preg_replace('/[-.]*/', '', $order->getBillingAddress()->getPostcode()));             
                $billingStreet1 = $order->getBillingAddress()->getStreet(1).' - '.$order->getBillingAddress()->getStreet(2);
                $billingStreet2 = $order->getBillingAddress()->getStreet(3).' - '.$order->getBillingAddress()->getStreet(4);            
            }
            if(is_object($order->getShippingAddress())){
                $shippingCustomerId = $order->getShippingAddress()->getCustomerId();
                $shippingCity = $order->getShippingAddress()->getCity();
                $shippingCustomerName = $order->getShippingAddress()->getName();
                $shippingEmail = $order->getShippingAddress()->getEmail() ? $order->getShippingAddress()->getEmail() : $order->getCustomerEmail();   
                $shippingTelephone = str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getShippingAddress()->getTelephone()));
                $shippingCellphone = str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getShippingAddress()->getCellPhone()));
                $shippingPostcode = str_replace(' ', '', preg_replace('/[-.]*/', '', $order->getShippingAddress()->getPostcode())); 
                $shippingStreet1 = $order->getShippingAddress()->getStreet(1).' - '.$order->getShippingAddress()->getStreet(2);
                $shippingStreet2 = $order->getShippingAddress()->getStreet(3).' - '.$order->getShippingAddress()->getStreet(4);
                if(is_object($order->getBillingAddress()) && ($order->getShippingAddress()->getRegionId() == $order->getBillingAddress()->getRegionId())){
                    $shippingRegion = $billingRegion;
                }else{
                    $directoryRegion = Mage::getResourceModel('directory/region_collection');
                    $directoryRegion->getSelect()->reset()->from(array('main_table' => $directoryRegion->getMainTable()), 'code');
                    $directoryRegion->addFieldToFilter('country_id','BR')->addFieldToFilter('region_id',$order->getShippingAddress()->getRegionId());
                    $shippingRegion = $directoryRegion->getResource()->getReadConnection()->fetchOne($directoryRegion->getSelect());      
                }
            }

            $this->limparParametros();

            //Define informação dos produtos
            $totalItems = 0;
            $items = $order->getAllItems();
            $this->Itens = array();
            
            $prodBundles = array();
            $prodConfigurable = array();
            foreach ($items as $arr){
                if($arr->getProductType()=="bundle"){
                      $prodBundles[] = array(
                          'price'=>$arr->getPrice(),
                          'id' => $arr->getItemId()
                      );
                }
                if($arr->getProductType()=="configurable"){
                      $prodConfigurable[] = array(
                          'price'=>$arr->getPrice(),
                          'id' => $arr->getItemId()
                      );
                }	                    
            }
            if(sizeof($prodConfigurable)>0){
                foreach ($prodConfigurable as $key => $conf){
                    foreach ($items as $arr){
                        if($conf['id']==$arr->getParentItemId()){
                            $arr->setPrice(number_format($conf['price'],3,'.',''));
                        }
                    }
                }   					
            }
            if(sizeof($prodBundles)>0){
                foreach ($prodBundles as $key => $bundle){
                    $n_filhos = 0;
                    foreach ($items as $arr){
                        if($bundle['id']==$arr->getParentItemId()){
                            $n_filhos++;                          
                        }
                    }
                    $prodBundles[$key]['n_filhos'] = $n_filhos;   
                }      
                foreach ($prodBundles as $key => $bundle){
                    foreach ($items as $arr){
                        if($bundle['id']==$arr->getParentItemId()){
                            $arr->setPrice(number_format($bundle['price']/$bundle['n_filhos'],3,'.',''));
                        }
                    }
                }
            }            
            
            $somaProdutos= 0;
            
            foreach ($items as $key =>  $item) {
                if($item->getProductType()!='configurable' 
                        && $item->getProductType()!='bundle' ){
                    $this->Itens[] = array('ID'=>$item->getProductId(),
                                           'Name'=>str_replace('&','',$item->getName()),
                                           'ItemValue'=>$item->getPrice(),
                                           'Qty'=>(int)$item->getQtyOrdered());
                    $totalItems += $item->getQtyOrdered();
                    $somaProdutos += $item->getPrice()*$item['qty_ordered'];
                }
            }        

            //Tratar descontos no pagamento
            $grandTotalSemFrete = $order->getGrandTotal()-$order->getShippingAmount();
            if(($grandTotalSemFrete) < $somaProdutos){
                    $discount_amount = abs($order->getDiscountAmount());
                    if(($somaProdutos-$discount_amount)>($grandTotalSemFrete)){
                        $discount_amount = $discount_amount+($somaProdutos-$discount_amount)-($grandTotalSemFrete);
                    }
                    $descontos = number_format((($grandTotalSemFrete)/(($grandTotalSemFrete)+$discount_amount)*100),3,'.','');
                    foreach($this->Itens as $key => $item){
                      $this->Itens[$key]['ItemValue'] = number_format((($descontos*$item['Qty'])*$item['ItemValue']/100)/$item['Qty'],3,'.','');
                    }                        
            }           
            
            $QtyInstallments = $order->getPayment()->getMethod() == 'gwap_cc' && !$order->getPayment()->getCcParcelas() ? $order->getPayment()->getCcParcelas() : 1;
            $ip = !isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? (isset($_SERVER['REMOTE_ADDR']) ? isset($_SERVER['REMOTE_ADDR']) : 0) : $_SERVER['HTTP_X_FORWARDED_FOR'];
            
            $gwap = Mage::getModel('gwap/order')->load($order->getId(),'order_id')->getClearsaleInfo();
            $payment = unserialize(Mage::helper('core')->decrypt($gwap));
            $cardNumber = $payment['cc_number'];
            $cardBin = $payment['cc_cid'];
            $sessionId = $payment['gwap_session_id'];
            
            $this->PedidoID = $order->getIncrementId();
            $this->SessionID = $sessionId;
            $this->Data = Mage::getModel('core/date')->date('Y-m-d').'T'.Mage::getModel('core/date')->date('H:i:s');
            $this->Email = $billingEmail;
            $this->ValorFrete = number_format($order->getShippingAmount(), 2,'.', '' );
            $this->PrazoEntrega = $order->getPrazoEntrega();
            //$this->ValorTotalItens = number_format($order->getGrandTotal()-$order->getShippingAmount(), 2,'.', '' );
            $this->ValorTotalPedido= number_format($order->getGrandTotal(), 2,'.', '' );
            $this->QtdItens = $totalItems;
            $this->QtdParcelas = $QtyInstallments;
            $this->IP = $ip;
            $this->Obs = 'Allpago';
            
            $BillingPhones = array();
            if($billingTelephone){
                $BillingPhones[] = array('Tipo' => '1', //Fixo
                                         'DDI' => 55,
                                         'DDD' => substr($billingTelephone, 0, 2),
                                         'Numero' => substr($billingTelephone, 2, strlen($billingTelephone)));
            }
            if($billingCellphone){
                $BillingPhones[] = array('Tipo' => '6', //Celular
                                         'DDI' => 55,
                                         'DDD' => substr($billingCellphone, 0, 2),
                                         'Numero' => substr($billingCellphone, 2, strlen($billingCellphone)));                
            }            
            $this->DadosCobranca = array('UsuarioID' => $billingCustomerId,
                                         'TipoUsuario' => 1, // 1 Pessoa Física
                                         'DocumentoLegal1' => $cpf,
                                         'Nome' => $billingCustomerName,
                                         'Email' => $billingEmail,   
                                         'Nascimento' => date('Y-m-d',strtotime(str_replace('/','-', $customer->getDob()))),
                                         'Sexo' => $customer->getGender()==2 ? 'M' : 'F',
                                         'Endereco' => array('Logradouro' => $order->getBillingAddress()->getStreet(1),
                                                             'Numero' => $order->getBillingAddress()->getNumber(),
                                                             'Complemento' => $order->getBillingAddress()->getComplement(),
                                                             'Bairro' => $order->getBillingAddress()->getNeighbourhood(),
                                                             'Cidade' => $billingCity,                                         
                                                             'UF' => $billingRegion,                                         
                                                             'Pais' => 'Brasil',                                                                                  
                                                             'CEP' => $billingPostcode),
                                         'Telefones' => array('Telefone' => $BillingPhones )

                                      );
            
            $ShippingPhones = array();
            if($shippingTelephone){
                $ShippingPhones[] = array('Tipo' => '1', //Fixo
                                          'DDI' => 55,
                                          'DDD' => substr($shippingTelephone, 0, 2),
                                          'Numero' => substr($shippingTelephone, 2, strlen($billingTelephone)));
            }
            if($shippingCellphone){
                $ShippingPhones[] = array('Tipo' => '6', //Celular
                                          'DDI' => 55,
                                          'DDD' => substr($shippingCellphone, 0, 2),
                                          'Numero' => substr($shippingCellphone, 2, strlen($shippingCellphone)));                
            }
            
            $this->DadosEntrega = array('UsuarioID' => $shippingCustomerId,
                                        'TipoUsuario' => 1, // 1 Pessoa Física
                                        'DocumentoLegal1' => $cpf,
                                        'Nome' => $shippingCustomerName,
                                        'Email' => $shippingEmail,   
                                        'Nascimento' => date('Y-m-d',strtotime(str_replace('/','-', $customer->getDob()))),
                                        'Sexo' => $customer->getGender()==2 ? 'M' : 'F',
                                        'Endereco' => array('Logradouro' => $order->getShippingAddress()->getStreet(1),
                                                            'Numero' => $order->getShippingAddress()->getNumber(),
                                                            'Complemento' => $order->getShippingAddress()->getComplement(),
                                                            'Bairro' => $order->getShippingAddress()->getNeighbourhood(),
                                                            'Cidade' => $shippingCity,                                         
                                                            'UF' => $shippingRegion,                                         
                                                            'Pais' => 'Brasil',                                                                                  
                                                            'CEP' => $shippingPostcode),
                                        'Telefones' => array('Telefone' => $ShippingPhones )

                                       );  

            /*            
            1  Cartão de Crédito
            2  Boleto Bancário
            3  Débito Bancário
            4  Débito Bancário – Dinheiro
            5  Débito Bancário – Cheque
            6  Transferência Bancária
            7  Sedex a Cobrar
            8  Cheque
            9  Dinheiro
            10  Financiamento 
            11  Fatura
            12  Cupom
            13  Multicheque
            14  Outros        
            *///$order->getPayment()->getMethod()
            $PaymentTypeID = 1;
            
            $this->Pagamentos = array('Data' => Mage::getModel('core/date')->date('Y-m-d').'T'.Mage::getModel('core/date')->date('H:i:s'),
                                      'Valor' => number_format($order->getGrandTotal(), 2,'.', '' ),
                                      'TipoPagamentoID' => $PaymentTypeID,
                                      'QtdParcelas' => $QtyInstallments,
                                      'HashNumeroCartao' => sha1($cardNumber),
                                      'Cartao4Ultimos' => $order->getPayment()->getCcLast4(),
                                      'BinCartao' => $cardBin,
                                      'TipoCartao' => Mage::helper('clearsale')->getCardType($order->getPayment()->getCcType()),
                                      'DataValidadeCartao' => $order->getPayment()->getCcExpMonth().'/'.$order->getPayment()->getCcExpYear(),
                                      'NomeTitularCartao' => utf8_decode($order->getPayment()->getCcOwner()));
        }catch(Exception $e){
            Mage::log(__METHOD__.' | Erro ao gerar array de parametros: '.$e->getMessage(),null,'erro_clearsale.log');
            exit;
        }
        
        try{
            $xml = $this->criarXml();
            
        }catch(Exception $e){
            Mage::log(__METHOD__.' | Erro ao gerar xml: '.$e->getMessage(),null,'erro_clearsale.log');
            exit;
        }
        
        return $xml;
    }
    
    /**
     *
     * @return stdClass
     */
    private function criarXml() {
        
        //Cria o objeto
        $params = new stdClass();
        $params->SessionID = $this->SessionID;
        $params->Pedido = new stdClass();
        //Order
        $params->Pedido->PedidoID = $this->PedidoID; 
        $params->Pedido->Data = $this->Data;
        $params->Pedido->Email = $this->Email;
        $params->Pedido->ValorFrete = $this->ValorFrete;
        $params->Pedido->PrazoEntrega = $this->PrazoEntrega;
        $params->Pedido->ValorTotalItens = $this->ValorTotalItens; 
        $params->Pedido->ValorTotalPedido = $this->ValorTotalPedido;
        $params->Pedido->QtdItens = $this->QtdItens;
        $params->Pedido->QtdParcelas = $this->QtdParcelas;
        $params->Pedido->IP = $this->IP;
        $params->Pedido->Obs = $this->Obs;
        //Items
        $params->Pedido->Itens = new stdClass();        
        $params->Pedido->Itens->Item = $this->Itens;
        //DadosCobranca
        
        $params->Pedido->DadosCobranca = new stdClass();
        $params->Pedido->DadosCobranca->UsuarioID = $this->DadosCobranca['UsuarioID']; 
        $params->Pedido->DadosCobranca->TipoUsuario = $this->DadosCobranca['TipoUsuario']; 
        $params->Pedido->DadosCobranca->DocumentoLegal1 = $this->DadosCobranca['DocumentoLegal1']; 
        $params->Pedido->DadosCobranca->Nome = $this->DadosCobranca['Nome']; 
        $params->Pedido->DadosCobranca->Email = $this->DadosCobranca['Email']; 
        $params->Pedido->DadosCobranca->Nascimento = $this->DadosCobranca['Nascimento']; 
        $params->Pedido->DadosCobranca->Sexo = $this->DadosCobranca['Sexo'];
        //DadosCobranca Endereço
        $params->Pedido->DadosCobranca->Endereco = new stdClass();
        $params->Pedido->DadosCobranca->Endereco->Logradouro = $this->DadosCobranca['Endereco']['Logradouro']; 
        $params->Pedido->DadosCobranca->Endereco->Numero = $this->DadosCobranca['Endereco']['Numero']; 
        $params->Pedido->DadosCobranca->Endereco->Complemento = $this->DadosCobranca['Endereco']['Complemento']; 
        $params->Pedido->DadosCobranca->Endereco->Bairro = $this->DadosCobranca['Endereco']['Bairro']; 
        $params->Pedido->DadosCobranca->Endereco->Cidade = $this->DadosCobranca['Endereco']['Cidade']; 
        $params->Pedido->DadosCobranca->Endereco->UF = $this->DadosCobranca['Endereco']['UF']; 
        $params->Pedido->DadosCobranca->Endereco->Pais = $this->DadosCobranca['Endereco']['Pais']; 
        $params->Pedido->DadosCobranca->Endereco->CEP = $this->DadosCobranca['Endereco']['CEP']; 
        //DadosCobranca Telefones
        $params->Pedido->DadosCobranca->Telefones = new stdClass();
        $params->Pedido->DadosCobranca->Telefones->Telefone = $this->DadosCobranca['Telefones']['Telefone'];        
        //DadosEntrega
        $params->Pedido->DadosEntrega = new stdClass();
        $params->Pedido->DadosEntrega->UsuarioID = $this->DadosEntrega['UsuarioID']; 
        $params->Pedido->DadosEntrega->TipoUsuario = $this->DadosEntrega['TipoUsuario']; 
        $params->Pedido->DadosEntrega->DocumentoLegal1 = $this->DadosEntrega['DocumentoLegal1']; 
        $params->Pedido->DadosEntrega->Nome = $this->DadosEntrega['Nome']; 
        $params->Pedido->DadosEntrega->Email = $this->DadosEntrega['Email']; 
        $params->Pedido->DadosEntrega->Nascimento = $this->DadosEntrega['Nascimento']; 
        $params->Pedido->DadosEntrega->Sexo = $this->DadosEntrega['Sexo'];
        //DadosEntrega Endereco
        $params->Pedido->DadosEntrega->Endereco = new stdClass();
        $params->Pedido->DadosEntrega->Endereco->Logradouro = $this->DadosEntrega['Endereco']['Logradouro']; 
        $params->Pedido->DadosEntrega->Endereco->Numero = $this->DadosEntrega['Endereco']['Numero']; 
        $params->Pedido->DadosEntrega->Endereco->Complemento = $this->DadosEntrega['Endereco']['Complemento']; 
        $params->Pedido->DadosEntrega->Endereco->Bairro = $this->DadosEntrega['Endereco']['Bairro']; 
        $params->Pedido->DadosEntrega->Endereco->Cidade = $this->DadosEntrega['Endereco']['Cidade']; 
        $params->Pedido->DadosEntrega->Endereco->UF = $this->DadosEntrega['Endereco']['UF']; 
        $params->Pedido->DadosEntrega->Endereco->Pais = $this->DadosEntrega['Endereco']['Pais']; 
        $params->Pedido->DadosEntrega->Endereco->CEP = $this->DadosEntrega['Endereco']['CEP']; 
        //DadosEntrega Phones
        $params->Pedido->DadosEntrega->Telefones = new stdClass();
        $params->Pedido->DadosEntrega->Telefones->Telefone = $this->DadosEntrega['Telefones']['Telefone'];         
        //Pagamento
        $params->Pedido->Pagamentos = new stdClass();    
        $params->Pedido->Pagamentos->Pagamento = new stdClass();
        $params->Pedido->Pagamentos->Pagamento->Data = $this->Pagamentos['Data'];
        $params->Pedido->Pagamentos->Pagamento->Valor = $this->Pagamentos['Valor'];
        $params->Pedido->Pagamentos->Pagamento->TipoPagamentoID = $this->Pagamentos['TipoPagamentoID'];
        $params->Pedido->Pagamentos->Pagamento->QtdParcelas = $this->Pagamentos['QtdParcelas'];
        $params->Pedido->Pagamentos->Pagamento->HashNumeroCartao = $this->Pagamentos['HashNumeroCartao'];
        $params->Pedido->Pagamentos->Pagamento->Cartao4Ultimos = $this->Pagamentos['Cartao4Ultimos'];
        $params->Pedido->Pagamentos->Pagamento->BinCartao = $this->Pagamentos['BinCartao'];
        $params->Pedido->Pagamentos->Pagamento->TipoCartao = $this->Pagamentos['TipoCartao'];
        $params->Pedido->Pagamentos->Pagamento->DataValidadeCartao = $this->Pagamentos['DataValidadeCartao'];
        $params->Pedido->Pagamentos->Pagamento->NomeTitularCartao = $this->Pagamentos['NomeTitularCartao'];

        return $params;
    }    
    
    public function SubmitInfoID($order = null){

        if(is_object($order)){
            
            $log = Mage::getModel('allpago_mc/log');            
            
            $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($order->getId(),'order_id');
            //Muda o status e adiciona incrementId na tabela do gateway
            $gatewayPayment->setIncrementId($order->getIncrementId());             
            $gatewayPayment->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CLEARSALE);
            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
            $gatewayPayment->save();
            //Cria os dados na tabela da clearsale
            $clearsale = Mage::getModel('clearsale/orders');
            $clearsale->setIncrementId($order->getIncrementId());             
            $clearsale->setOrderId($order->getId());
            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CREATED);
            $clearsale->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));  
            $clearsale->save(); 
            
            //Define as informações do XML
            try {
                $params = $this->criarParametros($order);

                //Faz a requisição no webservice
                parent::SubmitInfo($params);

                $retorno = $this->getResult();

                //Se ocorreu um erro
                if((int)$retorno['StatusCode']) {
                    //Salva log
                    $log->add($clearsale->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', 'Status: '.(string)$retorno['StatusCode'].' | Mensagem: '.$retorno['Message']);
                    //Define retorno na tabela
                    $clearsale->setErrorCode($retorno['StatusCode']);
                    $clearsale->setErrorMessage($retorno['Message']);
                    $clearsale->setStatus('error');
                    $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                    $clearsale->save();
                    
                }else{
                    
                    //Análise aprovada
                    if($retorno['Status'] == 'APA') {
                            //Salva log
                            $log->add($clearsale->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_APPROVED, 'Análise aprovada', $this->getStatusType($retorno['Status']));
                            //Define retorno na tabela
                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CAPTUREPAYMENT);
                            $clearsale->setStatusClearsale($this->getStatusType($retorno['Status']));
                            $clearsale->setScore($retorno['Score']);
                            $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $clearsale->save(); 
                            
                            //Tela de sucesso
                            $block = Mage::app()->getLayout()->getMessagesBlock();
                            $block->addSuccess('Transação autorizada com sucesso');
                            
                    // Análise reprovada    
                    }elseif(in_array($retorno['Status'], array('RPP','RPA'))){

                        $log->add($clearsale->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_DENIED, 'Análise reprovada', $this->getStatusType($retorno['Status']));

                        $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_DENIED);
                        $clearsale->setStatusClearsale($this->getStatusType($retorno['Status']));
                        $clearsale->setScore($retorno['Score']);
                        $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                        $clearsale->save(); 
                        
                        //Cancelar pedido reprovado
                        if(Mage::getStoreConfig('allpago/clearsale/cancelamento')){
                            $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($order->getId(), 'order_id');
                            $gatewayPayment->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
                            $gatewayPayment->setInfo(null);
                            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $gatewayPayment->save();
                            //Cancela o pedido
                            Mage::getModel('sales/order')->load($clearsale->getOrderId())->cancel()->save();
                        } 
                        //Tela de falha
                        $this->failureRedirect($retorno['Message']);
                        
                    //Analise com questionario pendente    
                    }elseif($retorno['Status'] == 'PAV') {
                        //Salva log
                        $log->add($clearsale->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_SEND, 'Questionário pendente', $this->getStatusType($retorno['Status']));
                        //Define os novos dados
                        $clearsale->setErrorCode(null);
                        $clearsale->setErrorMessage(null);
                        $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_SEND);
                        $clearsale->setStatusClearsale($this->getStatusType($retorno['Status']));
                        $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                        $clearsale->save(); 
                        
                        //Redirecionar cliente para questionário
                        Mage::app()->getFrontController()->getResponse()->setRedirect($retorno['URLQuestionario']);
                        Mage::app()->getResponse()->sendResponse();
                        exit;                       
                    }
                }
                
            } catch (Exception $e) {
                //Salva log
                $log->add($clearsale->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());
                $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $clearsale->save();                  
            }
            
        }
        
    }  
    
    public function SubmitInfoSimple(){

        //Obtém e bloqueia o recurso
        $locker = $this->criaLock('clearsale/locker', 'clearsale_send_orders');        
        $log = Mage::getModel('allpago_mc/log');
        
        //Carrega todos os pedidos autorizados
        $gatewayPayments = Mage::getModel('allpago_mc/payment')->getCollection()
                          ->addStatusFilter('authorized')
                          ->addTypeFilter('cc');
        
        //Percorre todos os pedidos autorizados
        foreach ($gatewayPayments as $gatewayPayment) {
            //Muda o status da tabela do gateway
            
            $gatewayPayment->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CLEARSALE);
            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
            $gatewayPayment->save();
            //Define os dados
            $clearsale = Mage::getModel('clearsale/orders');
            $clearsale->setOrderId($gatewayPayment->getOrderId());
            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CREATED);
            $clearsale->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
            $clearsale->save();
        }        
        
        //Carrega todos os pedidos do Clearsale
        $clearsaleOrders = Mage::getModel('clearsale/orders')->getCollection()
                ->addTimeFilter()
                ->addStatusFilter('created');
        
        //Percorre todos os pedidos criados
        foreach ($clearsaleOrders as $clearsaleOrder) {
            
            //Pega os dados do Pedido
            $order = Mage::getModel('sales/order')->load($clearsaleOrder->getOrderId());
            
            //Insere incrementId na tabela de pagamento e clearsale
            if(!$clearsaleOrder->getIncrementId()){
                $IncrementId = Mage::getModel('sales/order')->load($clearsaleOrder->getOrderId())->getIncrementId();
                $clearsaleOrder->setIncrementId($IncrementId);                  
                $clearsaleOrder->save();
                $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($clearsaleOrder->getOrderId(), 'order_id');
                $gatewayPayment->setIncrementId($IncrementId);                  
                $gatewayPayment->save();                
            }
            
            //Se o número de tentativas for menor que o máximo
            if ($clearsaleOrder->getTries() < $this->qtd_tentativas && $clearsaleOrder->getStatus() != Allpago_Clearsale_Model_Orders::STATUS_MAXTRIES) {
                
                //Define as informações do XML
                try {
                    $params = $this->criarParametros($order);
                    
                    //Faz a requisição no webservice
                    parent::SubmitInfo($params);

                    $retorno = $this->getResult();
                    
                    //Se ocorreu um erro
                    if((int)$retorno['StatusCode']) {
                        //Salva log
                        $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', 'Status: '.(string)$retorno['StatusCode'].' | Mensagem: '.$retorno['Message']);
                        //Define os novos dados
                        $clearsaleOrder->setErrorCode($retorno['StatusCode']);
                        $clearsaleOrder->setErrorMessage($retorno['Message']);
                        $clearsaleOrder->setStatus('error');
                    } else {
                        //Salva log
                        $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_SEND, 'O pedido foi enviado para análise', null);
                        //Define os novos dados
                        $clearsaleOrder->setErrorCode(null);
                        $clearsaleOrder->setErrorMessage(null);
                        $clearsaleOrder->setStatus(Allpago_Clearsale_Model_Orders::STATUS_SEND);
                    }

                } catch (Exception $e) {
                    //Salva log
                    $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'SubmitInfo()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());

                    //Incrementa as tentativas
                    $clearsaleOrder->setTries($clearsaleOrder->getTries() + 1);                      
                    
                }

                //Salva as informações padrão
                $clearsaleOrder->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $clearsaleOrder->save();
            }

            //Se o número de tentativas atingiu o máximo
            else { 
                
                //Salva log
                $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'sendOrders()', Allpago_Clearsale_Model_Orders::STATUS_MAXTRIES, 'Número máximo de tentativas excedido');
                //Limpa os dados sensíveis
                $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($clearsaleOrder->getOrderId(), 'order_id');
                $gatewayPayment->setInfo(null);
                $gatewayPayment->setAbandoned(1);
                $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $gatewayPayment->save();
                //Define os dados da tabela auxiliar
                $clearsaleOrder->setStatus(Allpago_Clearsale_Model_Orders::STATUS_MAXTRIES)->save();
                //Muda o status do pedido
                $order->cancel()->save();
            }
            // Limpa instancia do pedido atual.
            $order->clearInstance();
            
        }   

        //Desaloca o recurso
        $locker->unlock();        
    }    
    
    public function CheckOrderStatus() {
        
        //Obtém e bloqueia o recurso
        $locker = $this->criaLock('clearsale/locker', 'clearsale_return_analysis');

        $log = Mage::getModel('allpago_mc/log');

        try {
            
            //Carrega todos os pedidos para análise no clearsale
            $gatewayPayments = Mage::getModel('allpago_mc/payment')->getCollection()->addStatusFilter('clearsale');

            $OrdersList = array();
            foreach ($gatewayPayments as $gatewayPayment) {
                
                //Pega os dados do Pedido
                $order = Mage::getModel('sales/order')->load($gatewayPayment->getOrderId());

                /* @var $order Mage_Sales_Model_Order */
                if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
                    $gatewayPayment->setInfo(null);
                    $gatewayPayment->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
                    $gatewayPayment->setAbandoned(1);
                    $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));

                    $gatewayPayment->save();

                    continue;
                }                
                
                $OrdersList[] = $gatewayPayment->getIncrementId();                
            }            

            //Faz a requisição no webservice
            parent::CheckOrderStatus($OrdersList);

            //Se existirem análises a serem tratadas
            if (sizeof($this->getResult())) {

                //Cria os objetos necessários
                $clearsale = Mage::getModel('clearsale/orders');

                //Para cada análise encontrada
                foreach ($this->getResult() as $analise) {

                    //Confirma o recebimento da captura
                    $clearsale->load($analise['ID'], 'increment_id');   
                    
                    // Análise aprovada
                    if ($clearsale->getStatus() == 'analise'){ 
                        if(in_array($analise['Status'], array('APA','APQ',))) {

                            $log->add($clearsale->getOrderId(), 'Clearsale', 'CheckOrderStatus()', Allpago_Clearsale_Model_Orders::STATUS_APPROVED, 'Análise aprovada', $this->getStatusType($analise['Status']));

                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CAPTUREPAYMENT);
                            $clearsale->setStatusClearsale($this->getStatusType($analise['Status']));
                            $clearsale->setScore($analise['Score']);
                            $clearsale->setTries($clearsale->getTries() + 1);

                        // Análise reprovada    
                        }elseif(!in_array($analise['Status'], array('RPQ','RPP','RPA'))){

                            $log->add($clearsale->getOrderId(), 'Clearsale', 'CheckOrderStatus()', Allpago_Clearsale_Model_Orders::STATUS_DENIED, 'Análise reprovada', $this->getStatusType($analise['Status']));

                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_DENIED);
                            $clearsale->setStatusClearsale($this->getStatusType($analise['Status']));
                            $clearsale->setScore($analise['Score']);
                            $clearsale->setTries($clearsale->getTries() + 1);

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
                            
                        }

                    }

                    //Salva os dados na tabela
                    $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                    $clearsale->save();                         
                    
                    //Aprovação manual
                    if ($clearsale->getStatusClearsale() == 'APRLOJA' && ($clearsale->getStatus() == 'denied' || $clearsale->getStatus() == 'analise') ){
                            $log->add($clearsale->getOrderId(), 'Clearsale', 'CheckOrderStatus()', Allpago_Clearsale_Model_Orders::STATUS_APPROVED, 'Análise aprovada', 'Aprovação manual da Loja');
                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CAPTUREPAYMENT); 
                            //Salva os dados na tabela
                            $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                            $clearsale->save();                             
                    }
                    
                }
                
            }
            
        } catch (Exception $e) {
            //Salva log
            Mage::log(__METHOD__.' | Erro ao capturar: '.$e->getMessage(),null,'erro_clearsale.log');
        }

        //Desaloca o recurso
        $locker->unlock();
    }    
    
   
    public function failureRedirect($errorMsg) {
        $block = Mage::app()->getLayout()->getMessagesBlock();
        $block->addError('Pedido reprovado no anti-fraude (' . $errorMsg . ')');
        Mage::app()
                ->getResponse()
                ->setRedirect(Mage::getUrl('allpago_gwap/checkout/failure'));
        Mage::app()
                ->getResponse()
                ->sendResponse();
        exit;
    }
    
    
}

?>
