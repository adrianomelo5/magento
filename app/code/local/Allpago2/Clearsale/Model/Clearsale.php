<?php

class Allpago_Clearsale_Model_Clearsale extends Allpago_Clearsale_Model_Abstract {

    private $ID;
    private $SessionID;    
    private $Date;
    private $Email;
    private $B2B_B2C;
    private $ShippingPrice;
    private $DeliveryTimeCD;
    private $TotalItens;
    private $TotalOrder;
    private $QtyInstallments;
    private $QtyItems;
    private $QtyPaymentTypes;
    private $IP;
    private $GiftMessage;
    private $Obs;
    private $Status;
    private $Reanalise;
    private $Origin;
    private $ReservationDate;
    
    private $Items;
    private $BillingData;
    private $ShippingData;
    private $Payments;
    
    private $qtd_tentativas = 3;
    
    public function limparParametros(){
        $this->ID = null;
        $this->SessionID = null;        
        $this->Date = null;
        $this->Email = null;
        $this->B2B_B2C = null;
        $this->ShippingPrice = null;
        $this->DeliveryTimeCD = null;        
        
        $this->TotalItens = null;
        $this->TotalOrder = null;
        $this->QtyInstallments = null;
        $this->QtyItems = null;
        $this->QtyPaymentTypes = null;
        $this->IP = null;
        $this->GiftMessage = null;
        $this->Obs = null;
        $this->Status = null;
        $this->Reanalise = null;
        $this->Origin = null;
        $this->ReservationDate = null;
        
        $this->Items = array();
        $this->BillingData = array();
        $this->ShippingData = array();
        $this->Payments = array();
    }
    
    public function __construct(){
        $this->qtd_tentativas = (Mage::getStoreConfig('allpago/clearsale/qtd_tentativas')) ? Mage::getStoreConfig('allpago/clearsale/qtd_tentativas') : $this->qtd_tentativas;
    }
    
   /*     
    1  Diners
    2  MasterCard
    3  Visa
    4  Outros
    5  American Express
    6  HiperCard
    7  Aura      
    */
    public function getCardType($card) {
        
        $arrayCard = array('VISA'=>3,
                           'MASTER'=>2,
                           'AMEX'=>5,
                           'DINERS'=>1,
                           'HIPERCARD'=>2);
        
        return array_key_exists($card, $arrayCard) ? $arrayCard[$card] : 4;
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
            $this->Items = array();
            
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
                    $this->Items[] = array('ID'=>$item->getProductId(),
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
                    foreach($this->Items as $key => $item){
                      $this->Items[$key]['ItemValue'] = number_format((($descontos*$item['Qty'])*$item['ItemValue']/100)/$item['Qty'],3,'.','');
                    }                        
            }           
            
            $QtyInstallments = $order->getPayment()->getMethod() == 'gwap_cc' && !$order->getPayment()->getCcParcelas() ? $order->getPayment()->getCcParcelas() : 1;
            $ip = !isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? (isset($_SERVER['REMOTE_ADDR']) ? isset($_SERVER['REMOTE_ADDR']) : 0) : $_SERVER['HTTP_X_FORWARDED_FOR'];
            
            $gwap = Mage::getModel('gwap/order')->load($order->getId(),'order_id')->getClearsaleInfo();
            $payment = unserialize(Mage::helper('core')->decrypt($gwap));
            $cardNumber = $payment['cc_number'];
            $cardBin = $payment['cc_cid'];
            $sessionId = $payment['gwap_session_id'];
            
            $this->ID = $order->getIncrementId();
            $this->SessionID = $sessionId;
            $this->Date = Mage::getModel('core/date')->date('Y-m-d').'T'.Mage::getModel('core/date')->date('H:i:s');
            $this->Email = $billingEmail;
            $this->ShippingPrice = number_format($order->getShippingAmount(), 2,'.', '' );
            $this->DeliveryTimeCD = $order->getPrazoEntrega();
            $this->TotalItems = number_format($order->getGrandTotal()-$order->getShippingAmount(), 2,'.', '' );
            $this->TotalOrder= number_format($order->getGrandTotal(), 2,'.', '' );
            $this->QtyItems = $totalItems;
            $this->QtyInstallments = $QtyInstallments;
            $this->IP = $ip;
            $this->Obs = 'Allpago';
            $this->Origin = 'Loja'; 
            
            $BillingPhones = array();
            if($billingTelephone){
                $BillingPhones[] = array('Type' => '1', //Fixo
                                          'DDI' => 55,
                                          'DDD' => substr($billingTelephone, 0, 2),
                                          'Number' => preg_replace("/[^a-zA-Z0-9\s]/", "", substr($billingTelephone, 2, strlen($billingTelephone))));
            }
            if($billingCellphone){
                $BillingPhones[] = array('Type' => '6', //Celular
                                          'DDI' => 55,
                                          'DDD' => substr($billingCellphone, 0, 2),
                                          'Number' => preg_replace("/[^a-zA-Z0-9\s]/", "", substr($billingCellphone, 2, strlen($billingCellphone))));                
            }            
            $this->BillingData = array('ID' => $billingCustomerId,
                                         'Type' => 1, // 1 Pessoa Física
                                         'LegalDocument1' => $cpf,
                                         'Name' => $billingCustomerName,
                                         'Email' => $billingEmail,   
                                         'BirthDate' => date('Y-m-d',strtotime(str_replace('/','-', $customer->getDob()))),
                                         'Gender' => $customer->getGender()==2 ? 'M' : 'F',
                                         'Address' => array('Street' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getBillingAddress()->getStreet(1)),
                                                            'Number' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getBillingAddress()->getNumber()),
                                                            'Comp' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getBillingAddress()->getComplement()),
                                                            'County' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getBillingAddress()->getNeighbourhood()),
                                                            'City' => preg_replace("/[^a-zA-Z0-9\s]/", "", $billingCity),                                       
                                                            'State' => $billingRegion,                                         
                                                            'Country' => 'Brasil',                                                                                  
                                                            'ZipCode' => $billingPostcode),
                                         'Phones' => array('Phone' => $BillingPhones )

                                      );
            
            $ShippingPhones = array();
            if($shippingTelephone){
                $ShippingPhones[] = array('Type' => '1', //Fixo
                                          'DDI' => 55,
                                          'DDD' => substr($shippingTelephone, 0, 2),
                                          'Number' => preg_replace("/[^a-zA-Z0-9\s]/", "", substr($shippingTelephone, 2, strlen($billingTelephone))));
            }
            if($shippingCellphone){
                $ShippingPhones[] = array('Type' => '6', //Celular
                                          'DDI' => 55,
                                          'DDD' => substr($shippingCellphone, 0, 2),
                                          'Number' => preg_replace("/[^a-zA-Z0-9\s]/", "", substr($shippingCellphone, 2, strlen($shippingCellphone))));                
            }
            
            $this->ShippingData = array('ID' => $shippingCustomerId,
                                         'Type' => 1, // 1 Pessoa Física
                                         'LegalDocument1' => $cpf,
                                         'Name' => $shippingCustomerName,
                                         'Email' => $shippingEmail,   
                                         'BirthDate' => date('Y-m-d',strtotime(str_replace('/','-', $customer->getDob()))),
                                         'Gender' => $customer->getGender()==2 ? 'M' : 'F',
                                         'Address' => array('Street' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getShippingAddress()->getStreet(1)),
                                                            'Number' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getShippingAddress()->getNumber()),
                                                            'Comp' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getShippingAddress()->getComplement()),
                                                            'County' => preg_replace("/[^a-zA-Z0-9\s]/", "", $order->getShippingAddress()->getNeighbourhood()),
                                                            'City' => preg_replace("/[^a-zA-Z0-9\s]/", "", $shippingCity),
                                                            'State' => $shippingRegion,                                         
                                                            'Country' => 'Brasil',                                                                                  
                                                            'ZipCode' => $shippingPostcode),
                                         'Phones' => array('Phone' => $ShippingPhones )

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
            
            $this->Payments = array('Date' => Mage::getModel('core/date')->date('Y-m-d').'T'.Mage::getModel('core/date')->date('H:i:s'),
                                    'Amount' => number_format($order->getGrandTotal(), 2,'.', '' ),
                                    'PaymentTypeID' => $PaymentTypeID,
                                    'QtyInstallments' => $QtyInstallments,
                                    'CardEndNumber' => $order->getPayment()->getCcLast4(),
                                    'CardNumber' => $cardNumber,
                                    'CardBin' => $cardBin,
                                    'CardType' => $this->getCardType($order->getPayment()->getCcType()),
                                    'CardExpirationDate' => $order->getPayment()->getCcExpMonth().'/'.$order->getPayment()->getCcExpYear(),
                                    'Name' => utf8_decode($order->getPayment()->getCcOwner()));
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
        $params->Orders = new stdClass();
        $params->Orders->Order = new stdClass();
        //Order
        $params->Orders->Order->ID = $this->ID; 
//        $params->Orders->Order->FingerPrint = new stdClass();
//        $params->Orders->Order->FingerPrint->SessionID = $this->SessionID;
        $params->Orders->Order->Date = $this->Date;
        $params->Orders->Order->Email = $this->Email;
        $params->Orders->Order->ShippingPrice = $this->ShippingPrice;
        $params->Orders->Order->TotalOrder = $this->TotalOrder;        
        $params->Orders->Order->DeliveryTimeCD = $this->DeliveryTimeCD;
        $params->Orders->Order->TotalItems = $this->TotalItems; 
        $params->Orders->Order->QtyItems = $this->QtyItems;
        $params->Orders->Order->QtyInstallments = $this->QtyInstallments;
        $params->Orders->Order->IP = $this->IP;
        $params->Orders->Order->Obs = $this->Obs;
        $params->Orders->Order->Origin = $this->Origin;   
        //BillingData
        $params->Orders->Order->BillingData = new stdClass();
        $params->Orders->Order->BillingData->ID = $this->BillingData['ID']; 
        $params->Orders->Order->BillingData->Type = $this->ShippingData['Type']; 
        $params->Orders->Order->BillingData->LegalDocument1 = $this->BillingData['LegalDocument1']; 
        $params->Orders->Order->BillingData->Name = $this->BillingData['Name']; 
        $params->Orders->Order->BillingData->BirthDate = $this->BillingData['BirthDate'];         
        $params->Orders->Order->BillingData->Email = $this->BillingData['Email']; 
        $params->Orders->Order->BillingData->Gender = $this->BillingData['Gender'];
        //BillingData Address
        $params->Orders->Order->BillingData->Address = new stdClass();
        $params->Orders->Order->BillingData->Address->Street = $this->BillingData['Address']['Street']; 
        $params->Orders->Order->BillingData->Address->Number = $this->BillingData['Address']['Number']; 
        $params->Orders->Order->BillingData->Address->Comp = $this->BillingData['Address']['Comp']; 
        $params->Orders->Order->BillingData->Address->County = $this->BillingData['Address']['County']; 
        $params->Orders->Order->BillingData->Address->City = $this->BillingData['Address']['City']; 
        $params->Orders->Order->BillingData->Address->State = $this->BillingData['Address']['State']; 
        $params->Orders->Order->BillingData->Address->Country = $this->BillingData['Address']['Country']; 
        $params->Orders->Order->BillingData->Address->ZipCode = $this->BillingData['Address']['ZipCode']; 
        //BillingData Phones
        $params->Orders->Order->BillingData->Phones = new stdClass();
        $params->Orders->Order->BillingData->Phones->Phone = $this->BillingData['Phones']['Phone'];        
        //ShippingData
        $params->Orders->Order->ShippingData = new stdClass();
        $params->Orders->Order->ShippingData->ID = $this->ShippingData['ID']; 
        $params->Orders->Order->ShippingData->Type = $this->ShippingData['Type']; 
        $params->Orders->Order->ShippingData->LegalDocument1 = $this->ShippingData['LegalDocument1']; 
        $params->Orders->Order->ShippingData->Name = $this->ShippingData['Name']; 
        $params->Orders->Order->ShippingData->BirthDate = $this->ShippingData['BirthDate'];         
        $params->Orders->Order->ShippingData->Email = $this->ShippingData['Email']; 
        $params->Orders->Order->ShippingData->BirthDate = $this->ShippingData['BirthDate']; 
        $params->Orders->Order->ShippingData->Gender = $this->ShippingData['Gender'];
        //ShippingData Address
        $params->Orders->Order->ShippingData->Address = new stdClass();
        $params->Orders->Order->ShippingData->Address->Street = $this->ShippingData['Address']['Street']; 
        $params->Orders->Order->ShippingData->Address->Number = $this->ShippingData['Address']['Number']; 
        $params->Orders->Order->ShippingData->Address->Comp = $this->ShippingData['Address']['Comp']; 
        $params->Orders->Order->ShippingData->Address->County = $this->ShippingData['Address']['County']; 
        $params->Orders->Order->ShippingData->Address->City = $this->ShippingData['Address']['City']; 
        $params->Orders->Order->ShippingData->Address->State = $this->ShippingData['Address']['State']; 
        $params->Orders->Order->ShippingData->Address->Country = $this->ShippingData['Address']['Country']; 
        $params->Orders->Order->ShippingData->Address->ZipCode = $this->ShippingData['Address']['ZipCode']; 
        //ShippingData Phones
        $params->Orders->Order->ShippingData->Phones = new stdClass();
        $params->Orders->Order->ShippingData->Phones->Phone = $this->ShippingData['Phones']['Phone'];         
        //Payments
        $params->Orders->Order->Payments = new stdClass();    
        $params->Orders->Order->Payments->Payment = new stdClass();
        $params->Orders->Order->Payments->Payment->Date = $this->Payments['Date'];
        $params->Orders->Order->Payments->Payment->Amount = $this->Payments['Amount'];
        $params->Orders->Order->Payments->Payment->PaymentTypeID = $this->Payments['PaymentTypeID'];
        $params->Orders->Order->Payments->Payment->QtyInstallments = $this->Payments['QtyInstallments'];
        $params->Orders->Order->Payments->Payment->CardEndNumber = $this->Payments['CardEndNumber'];
        //$params->Orders->Order->Payments->Payment->CardNumber = $this->Payments['CardNumber'];
        //$params->Orders->Order->Payments->Payment->CardBin = $this->Payments['CardBin'];
        $params->Orders->Order->Payments->Payment->CardType = $this->Payments['CardType'];
        $params->Orders->Order->Payments->Payment->CardExpirationDate = $this->Payments['CardExpirationDate'];
        $params->Orders->Order->Payments->Payment->Name = $this->Payments['Name'];
        //Items
        $params->Orders->Order->Items = new stdClass();        
        $params->Orders->Order->Items->Item = $this->Items;
        
        return $params;
    }    
    
    
    public function sendOrders(){

        if(!Mage::getStoreConfig('allpago/clearsale/active') 
                || Mage::getStoreConfig('allpago/clearsale/produto') != 'tg'){
            return false;
        }
        
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
                ->addTimeFilter(Mage::getStoreConfig('allpago/allpago_mc/tempo_espera'))
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
                    parent::SendOrders($params);

                    $retorno = $this->getResult();
                    
                    //Se ocorreu um erro
                    if((int)$retorno['StatusCode']) {
                        
                        //Pedido já enviado
                        if($retorno['StatusCode'] == '05'){
                            //Salva log
                            $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'sendOrders()', Allpago_Clearsale_Model_Orders::STATUS_SEND, 'O pedido foi enviado para análise', null);
                            //Define os novos dados
                            $clearsaleOrder->setErrorMessage(null);
                            $clearsaleOrder->setStatus(Allpago_Clearsale_Model_Orders::STATUS_SEND);                            
                        //Dados não enviados
                        }elseif($retorno['StatusCode'] == 10){
                           
                            if($clearsaleOrder->getErrorMessage() != $retorno['Message']){
                                //Salva log
                                $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'sendOrders()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', 'Status: '.(string)$retorno['StatusCode'].' | Mensagem: '.$retorno['Message']);
                                //Define os novos dados
                                $clearsaleOrder->setErrorMessage($retorno['Message']); 
                                $clearsaleOrder->setStatus('holded');
                            }

                        }else{
                            //Salva log
                            $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'sendOrders()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', 'Status: '.(string)$retorno['StatusCode'].' | Mensagem: '.$retorno['Message']);
                            //Define os novos dados
                            $clearsaleOrder->setErrorMessage($retorno['Message']);
                            $clearsaleOrder->setStatus('holded');
                        }
                    } else {
                        //Salva log
                        $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'sendOrders()', Allpago_Clearsale_Model_Orders::STATUS_SEND, 'O pedido foi enviado para análise', null);
                        //Define os novos dados
                        $clearsaleOrder->setErrorMessage(null);
                        $clearsaleOrder->setStatus(Allpago_Clearsale_Model_Orders::STATUS_SEND);
                    }

                } catch (Exception $e) {
                    //Salva log
                    $log->add($clearsaleOrder->getOrderId(), 'Clearsale', 'sendOrders()', Allpago_Clearsale_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());

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

    public function getOrdersStatus() {
        
        if(!Mage::getStoreConfig('allpago/clearsale/active')
                || Mage::getStoreConfig('allpago/clearsale/produto') != 'tg'){
            return false;
        }        
        
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
            parent::GetOrdersStatus($OrdersList);

            //Se existirem análises a serem tratadas
            if (sizeof($this->getResult())) {

                //Cria os objetos necessários
                $clearsale = Mage::getModel('clearsale/orders');

                //Para cada análise encontrada
                foreach ($this->getResult() as $analise) {

                    //Confirma o recebimento da captura
                    $clearsale->load($analise['ID'], 'increment_id');   
                    
                    // Análise aprovada
                    if ($clearsale->getStatus() == 'analise' || ($clearsale->getStatus() == 'denied' && $clearsale->getStatusClearsale() != 'LOJA') ){ 
                        if(in_array($analise['Status'], array('APA','APM'))) {

                            $log->add($clearsale->getOrderId(), 'Clearsale', 'getOrdersStatus()', Allpago_Clearsale_Model_Orders::STATUS_APPROVED, 'Análise aprovada', $this->getStatusType($analise['Status']));

                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_CAPTUREPAYMENT);
                            $clearsale->setStatusClearsale($this->getStatusType($analise['Status']));
                            $clearsale->setScore($analise['Score']);
                            //$clearsale->setErrorCode(null);
                            //$clearsale->setErrorMessage(null);
                            //$clearsale->setInfo(serialize($analise));
                            $clearsale->setTries($clearsale->getTries() + 1);

                            $params = array('orderID'=>$clearsale->getIncrementId(),
                                            'statusPedido'=>'Aprovado');
                            parent::UpdateOrderStatusID($params);
                            $resultUpdateStatus = $this->getResultContent();
                            //Falha na confirmação de pagamento da análise
                            if(isset($resultUpdateStatus['StatusCode'])){
                                $log->add($clearsale->getOrderId(), 'Clearsale', 'updateOrderStatusID()', '', $resultUpdateStatus['StatusCode'], $resultUpdateStatus['Message']);
                            }                            
                            
                        // Análise reprovada    
                        }elseif(!in_array($analise['Status'], array('AMA','NVO','ERR'))){

                            if($clearsale->getStatus() != 'denied'){
                               $log->add($clearsale->getOrderId(), 'Clearsale', 'getOrdersStatus()', Allpago_Clearsale_Model_Orders::STATUS_DENIED, 'Análise reprovada', $this->getStatusType($analise['Status']));
                               $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_DENIED);
                            }

                            $clearsale->setStatus(Allpago_Clearsale_Model_Orders::STATUS_DENIED);
                            $clearsale->setStatusClearsale($this->getStatusType($analise['Status']));
                            $clearsale->setScore($analise['Score']);
                            //$clearsale->setErrorCode(null);
                            //$clearsale->setErrorMessage(null);
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
                            /*
                            $params = array('orderID'=>$clearsale->getIncrementId(),
                                            'statusPedido'=>'Reprovado');                            
                            parent::UpdateOrderStatusID($params);
                            
                            $resultUpdateStatus = $this->getResultContent();
                            //Falha no cancelamento de pagamento da análise
                            if(isset($resultUpdateStatus['StatusCode'])){
                                $log->add($clearsale->getOrderId(), 'Clearsale', 'updateOrderStatusID()', '', $resultUpdateStatus['StatusCode'], $resultUpdateStatus['Message']);
                            }
                            */
                            
                        }
                    }

                    //Salva os dados na tabela
                    $clearsale->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                    $clearsale->save();                         
                    
                    //Aprovação manual
                    if ($clearsale->getStatusClearsale() == 'APRLOJA' && ($clearsale->getStatus() == 'denied' || $clearsale->getStatus() == 'analise') ){
                            $log->add($clearsale->getOrderId(), 'Clearsale', 'getOrdersStatus()', Allpago_Clearsale_Model_Orders::STATUS_APPROVED, 'Análise aprovada', 'Aprovação manual da Loja');
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
    
   
}

?>
