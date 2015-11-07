<?php

/**
 * Allpago Module for Fcontrol
 *
 * @title      Magento -> Custom Module for Fcontrol
 * @category   Fraud Control Gateway
 * @package    Allpago_Fcontrol
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
class Allpago_Fcontrol_Model_Standard extends Mage_Core_Model_Abstract {

    //Variáveis utilizadas pelo Gateway
    private $ws_url = '';
    private $ws_login = '';
    private $ws_pass = '';
    private $qtd_tentativas = 3;
    private $qtd_analises = 10;
    private $country = 'BRA';
    private $xmlCompradorNome = '';
    private $xmlCompradorCpfCnpj = '';
    private $xmlCompradorSexo = '';
    private $xmlCompradorDataNascimento = '';
    private $xmlCompradorDddTelefone = '';
    private $xmlCompradorNumeroTelefone = '';
    private $xmlCompradorDddTelefone2 = '';
    private $xmlCompradorNumeroTelefone2 = '';
    private $xmlCompradorDddCelular = '';
    private $xmlCompradorNumeroCelular = '';
    private $xmlCompradorIP = '';
    private $xmlCompradorEmail = '';
    private $xmlCompradorSenha = '';
    private $xmlCompradorEnderecoCep = '';
    private $xmlCompradorEnderecoRua = '';
    private $xmlCompradorEnderecoNumero = '';
    private $xmlCompradorEnderecoComplemento = '';
    private $xmlCompradorEnderecoBairro = '';
    private $xmlCompradorEnderecoCidade = '';
    private $xmlCompradorEnderecoEstado = '';
    private $xmlEntregaNome = '';
    private $xmlEntregaCpfCnpj = '';
    private $xmlEntregaSexo = '';
    private $xmlEntregaDataNascimento = '';
    private $xmlEntregaDddTelefone = '';
    private $xmlEntregaNumeroTelefone = '';
    private $xmlEntregaDddTelefone2 = '';
    private $xmlEntregaNumeroTelefone2 = '';
    private $xmlEntregaDddCelular = '';
    private $xmlEntregaNumeroCelular = '';
    private $xmlEntregaEnderecoCep = '';
    private $xmlEntregaEnderecoRua = '';
    private $xmlEntregaEnderecoNumero = '';
    private $xmlEntregaEnderecoComplemento = '';
    private $xmlEntregaEnderecoBairro = '';
    private $xmlEntregaEnderecoCidade = '';
    private $xmlPedidoCodigoPedido = '';
    private $xmlPedidoDataCompra = '';
    private $xmlPedidoQuantidadeItensDistintos = '';
    private $xmlPedidoQuantidadeTotalItens = '';
    private $xmlPedidoValorTotalCompra = '';
    private $xmlPedidoValorTotalFrete = '';
    private $xmlPedidoPedidoDeTeste = '';
    private $xmlPedidoPrazoEntregaDias = '';
    private $xmlPedidoFormaEntrega = '';
    private $xmlPedidoObservacao = '';
    private $xmlPedidoCanalVenda = '';
    private $xmlPedidoProdutos = array();
    private $xmlPedidoPagamentos = array();
    private $xmlPedidoStatusCodigo = '';
    private $xmlPedidoMotivoCodigo = '0';
    private $xmlPedidoComentario = '';
    private $xmlPedidoCompartilharComentario = 'false';
    private $xmlDadosExtra1 = '';
    private $xmlDadosExtra2 = '';
    private $xmlDadosExtra3 = '';
    private $xmlDadosExtra4 = '';

    
    private function _resetData(){
        $this->xmlCompradorNome = '';
        $this->xmlCompradorCpfCnpj = '';
        $this->xmlCompradorSexo = '';
        $this->xmlCompradorDataNascimento = '';
        $this->xmlCompradorDddTelefone = '';
        $this->xmlCompradorNumeroTelefone = '';
        $this->xmlCompradorDddTelefone2 = '';
        $this->xmlCompradorNumeroTelefone2 = '';
        $this->xmlCompradorDddCelular = '';
        $this->xmlCompradorNumeroCelular = '';
        $this->xmlCompradorIP = '';
        $this->xmlCompradorEmail = '';
        $this->xmlCompradorSenha = '';
        $this->xmlCompradorEnderecoCep = '';
        $this->xmlCompradorEnderecoRua = '';
        $this->xmlCompradorEnderecoNumero = '';
        $this->xmlCompradorEnderecoComplemento = '';
        $this->xmlCompradorEnderecoBairro = '';
        $this->xmlCompradorEnderecoCidade = '';
        $this->xmlCompradorEnderecoEstado = '';
        $this->xmlEntregaNome = '';
        $this->xmlEntregaCpfCnpj = '';
        $this->xmlEntregaSexo = '';
        $this->xmlEntregaDataNascimento = '';
        $this->xmlEntregaDddTelefone = '';
        $this->xmlEntregaNumeroTelefone = '';
        $this->xmlEntregaDddTelefone2 = '';
        $this->xmlEntregaNumeroTelefone2 = '';
        $this->xmlEntregaDddCelular = '';
        $this->xmlEntregaNumeroCelular = '';
        $this->xmlEntregaEnderecoCep = '';
        $this->xmlEntregaEnderecoRua = '';
        $this->xmlEntregaEnderecoNumero = '';
        $this->xmlEntregaEnderecoComplemento = '';
        $this->xmlEntregaEnderecoBairro = '';
        $this->xmlEntregaEnderecoCidade = '';
        $this->xmlPedidoCodigoPedido = '';
        $this->xmlPedidoDataCompra = '';
        $this->xmlPedidoQuantidadeItensDistintos = '';
        $this->xmlPedidoQuantidadeTotalItens = '';
        $this->xmlPedidoValorTotalCompra = '';
        $this->xmlPedidoValorTotalFrete = '';
        $this->xmlPedidoPedidoDeTeste = '';
        $this->xmlPedidoPrazoEntregaDias = '';
        $this->xmlPedidoFormaEntrega = '';
        $this->xmlPedidoObservacao = '';
        $this->xmlPedidoCanalVenda = '';
        $this->xmlPedidoProdutos = array();
        $this->xmlPedidoPagamentos = array();
        $this->xmlPedidoStatusCodigo = '';
        $this->xmlPedidoMotivoCodigo = '0';
        $this->xmlPedidoComentario = '';
        $this->xmlPedidoCompartilharComentario = 'false';
        $this->xmlDadosExtra1 = '';
        $this->xmlDadosExtra2 = '';
        $this->xmlDadosExtra3 = '';
        $this->xmlDadosExtra4 = '';
    }
    /**
     * Define initial data from config table
     *
     * @return boolean
     */
    private function defineConfig() {
        try {
            //Define os dados
            $this->ws_url = (Mage::getStoreConfig('allpago/fcontrol/ws_url')) ? Mage::getStoreConfig('allpago/fcontrol/ws_url') : $this->ws_url;
            $this->ws_login = (Mage::getStoreConfig('allpago/fcontrol/ws_login')) ? Mage::getStoreConfig('allpago/fcontrol/ws_login') : $this->ws_login;
            $this->ws_pass = (Mage::getStoreConfig('allpago/fcontrol/ws_pass')) ? Mage::getStoreConfig('allpago/fcontrol/ws_pass') : $this->ws_pass;
            $this->qtd_tentativas = (Mage::getStoreConfig('allpago/fcontrol/qtd_tentativas')) ? Mage::getStoreConfig('allpago/fcontrol/qtd_tentativas') : $this->qtd_tentativas;
            $this->qtd_analises = (Mage::getStoreConfig('allpago/fcontrol/qtd_analises')) ? Mage::getStoreConfig('allpago/fcontrol/qtd_analises') : $this->qtd_analises;
            $this->country = (Mage::getStoreConfig('allpago/fcontrol/country')) ? Mage::getStoreConfig('allpago/fcontrol/country') : $this->country;
            return true;
        } catch (Exception $e) {
            $log = Mage::getModel('allpago_mc/log');
            //Salva log
            $log->add(null, 'Fcontrol', 'defineConfig()', self::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());
        }
    }

    /**
     * Return the string corresponding to the status code
     *
     * @param int $status
     * @return string
     */
    private function getFcontrolStatusMessage($status) {
        //Define o status do resultado
        switch ($status) {
            case '2':
                return 'Enviado';
            case '3':
                return 'Cancelado';
            case '6':
                return 'Cancelado por Suspeita';
            case '7':
                return 'Aprovada';
            case '10':
                return 'Fraude Confirmada';
            case '13':
                return 'Não aprovado pela operadora do cartão';
        }
    }

    public function criaLock($model, $id) {
        $log = Mage::getModel('allpago_mc/log');
        try {
            //Obtém e bloqueia o recurso
            $locker = Mage::getModel($model);
            $locker->_id = $id;
            //Se o recurso já estiver alocado
            if ($locker->isLocked()) {
                //Cancela a execução
                //echo PHP_EOL . date('Y-m-d H:i:s') . ' Robo ' . $locker->_id . ' ja em execucao' . PHP_EOL;
                $log->add(null, 'Fcontrol', 'criaLock()', self::STATUS_ERROR, 'Robo ja em execucao', $e->getMessage());
                exit;
            }
            //Aloca o recurso
            $locker->lock();
            //Retorna o recurso
            return $locker;
        } catch (Exception $e) {
            //Salva log
            $log->add(null, 'Fcontrol', 'criaLock()', self::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());
        }
    }

    /**
     * Define the enqueue XML data
     *
     * @param Varien_Object $order
     */
    private function enfileirarData($order) {
        
        $this->_resetData();
        //Pega os dados do cliente
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        //Define os dados da cobrança
        $this->xmlCompradorNome = (string) $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
        $customerDocs = explode(",", Mage::getStoreConfig('allpago/fcontrol/campo_documento'));
       
        $cpfLog = null;
        
        foreach ($customerDocs as $customerDoc) {
            $metodo = 'get' . ucfirst($customerDoc);
            if (!$this->xmlCompradorCpfCnpj && $customer->$metodo()) {
                $this->xmlCompradorCpfCnpj = (string) preg_replace('/[^0-9]/', '', $customer->$metodo());
            }
            if ($customer->$metodo()){  
                $cpfLog .= '<pre>'.print_r($customer->getData(),true).'<br/>Método: '.$metodo.' ( '.$customer->$metodo() .' )</pre>' ;
            }
        }
        
        if(!$this->xmlCompradorCpfCnpj){
            Mage::throwException('CPF não encontrado'.$cpfLog);
        }
        
        $this->xmlCompradorSexo = (string) 'M'; // @todo: Pegar esta informação do Magento
        $this->xmlCompradorDataNascimento = (string) '1900-01-01'; // @todo: Pegar esta informação do Magento
        $this->xmlCompradorDddTelefone = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getTelephone())), 0, 2);
        $this->xmlCompradorNumeroTelefone = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getTelephone())), 2, 9);
        $this->xmlCompradorDddCelular = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getTelephone())), 0, 2); // @todo: Pegar esta informação do Magento / Community e o Enterprise
        $this->xmlCompradorNumeroCelular = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getTelephone())), 2, 9); // @todo: Pegar esta informação do Magento / Community e o Enterprise
        $this->xmlCompradorIP = (string) $order->getRemoteIp();
        $this->xmlCompradorEmail = (string) $order->getCustomerEmail();
        $this->xmlCompradorEnderecoCep = (string) str_replace(' ', '', preg_replace('/[-.]*/', '', $order->getBillingAddress()->getPostcode()));
        $this->xmlCompradorEnderecoRua = (string) $order->getBillingAddress()->getStreet(1);
        $this->xmlCompradorEnderecoNumero = (string) $order->getBillingAddress()->getStreet(2);
        $this->xmlCompradorEnderecoComplemento = (string) Mage::helper('core/string')->truncate($order->getBillingAddress()->getStreet(3),100);
        $this->xmlCompradorEnderecoBairro = (string) $order->getBillingAddress()->getStreet(4);
        $this->xmlCompradorEnderecoCidade = (string) $order->getBillingAddress()->getCity();
        
        $region = "";
       
        $directoryRegion = Mage::getResourceModel('directory/region_collection');
        $directoryRegion->getSelect()->reset()->from(array('main_table' => $directoryRegion->getMainTable()), 'default_name');
        $directoryRegion->addFieldToFilter('country_id','BR')->addFieldToFilter('region_id',$order->getBillingAddress()->getRegionId());

        $billingRegion = $directoryRegion->getResource()->getReadConnection()->fetchOne($directoryRegion->getSelect());

        $this->xmlCompradorEnderecoEstado = (string) ($billingRegion);
        //Define os dados de entrega
        $this->xmlEntregaNome = (string) ($order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname());
        $customerDocs = explode(",", Mage::getStoreConfig('allpago/fcontrol/campo_documento'));
        
        $this->xmlEntregaCpfCnpj = null;
        foreach ($customerDocs as $customerDoc) {
            $metodo = 'get' . ucfirst($customerDoc);
            if (!$this->xmlEntregaCpfCnpj && $order->getShippingAddress()->$metodo())
                $this->xmlEntregaCpfCnpj = (string) preg_replace('/[^0-9]/', '', $order->getShippingAddress()->$metodo());
        }
        $this->xmlEntregaCpfCnpj = ($this->xmlEntregaCpfCnpj) ? $this->xmlEntregaCpfCnpj : $this->xmlCompradorCpfCnpj;
        $this->xmlEntregaSexo = (string) 'M'; // @todo: Pegar esta informação do Magento
        $this->xmlEntregaDataNascimento = (string) '1900-01-01'; // @todo: Pegar esta informação do Magento
        $this->xmlEntregaDddTelefone = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getShippingAddress()->getTelephone())), 0, 2);
        $this->xmlEntregaNumeroTelefone = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getShippingAddress()->getTelephone())), 2, 9);
        $this->xmlEntregaDddCelular = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getBillingAddress()->getTelephone())), 0, 2);  // @todo: Pegar esta informação do Magento / Community e o Enterprise
        $this->xmlEntregaNumeroCelular = (string) substr(str_replace(' ', '', preg_replace('/[()-]*/', '', $order->getShippingAddress()->getTelephone())), 2, 9);  // @todo: Pegar esta informação do Magento / Community e o Enterprise
        $this->xmlEntregaEnderecoCep = (string) str_replace(' ', '', preg_replace('/[-.]*/', '', $order->getShippingAddress()->getPostcode()));
        $this->xmlEntregaEnderecoRua = (string) $order->getShippingAddress()->getStreet(1);
        $this->xmlEntregaEnderecoNumero = (string) $order->getShippingAddress()->getStreet(2);
        $this->xmlEntregaEnderecoComplemento = (string) Mage::helper('core/string')->truncate($order->getShippingAddress()->getStreet(3), 100); 
        $this->xmlEntregaEnderecoBairro = (string) $order->getShippingAddress()->getStreet(4);
        $this->xmlEntregaEnderecoCidade = (string) $order->getShippingAddress()->getCity();
       
        $directoryRegion = Mage::getResourceModel('directory/region_collection');
        $directoryRegion->getSelect()->reset()->from(array('main_table' => $directoryRegion->getMainTable()), 'default_name');
        $directoryRegion->addFieldToFilter('country_id','BR')->addFieldToFilter('region_id',$order->getShippingAddress()->getRegionId());

        $shippingRegion = $directoryRegion->getResource()->getReadConnection()->fetchOne($directoryRegion->getSelect());

        $this->xmlEntregaEnderecoEstado = (string) $shippingRegion;
        //Define os dados dos produtos
        $totalItems = 0;
        $items = $order->getAllItems();
        $this->xmlPedidoProdutos = array();
        foreach ($items as $item) {
            $this->xmlPedidoProdutos[] = array(
                'Codigo' => (string) $item->getProductId(),
                'Descricao' => (string) $item->getName(),
                'Quantidade' => (string) $item->getQtyOrdered(),
                'ValorUnitario' => (string) $item->getPrice() * 100,
                'ListaDeCasamento' => (string) false,
                'ParaPresente' => (string) false
            );
            $totalItems += $item->getQtyOrdered();
        }
        //Define os dados do pagamento
        $this->xmlPedidoPagamentos = array(
            'MetodoPagamento' => (string) 'CartaoCredito',
            'Valor' => (string) ($order->getGrandTotal() * 100),
            'NumeroParcelas' => (string) 1 // @todo: buscar dados reais, independente do gateway
        );
        //Define os dados dos pedido
        $this->xmlPedidoCodigoPedido = (string) $order->getId();
        $dataCompra = new DateTime($order->getCreatedAt());
        $dataCompra->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        $this->xmlPedidoDataCompra = (string) $dataCompra->format('Y-m-d\TH:i:s');
        $this->xmlPedidoQuantidadeItensDistintos = (string) count($items);
        $this->xmlPedidoQuantidadeTotalItens = (string) $totalItems;
        $this->xmlPedidoValorTotalCompra = (string) ($order->getGrandTotal() * 100);
        $this->xmlPedidoValorTotalFrete = (string) ($order->getPayment()->getShippingAmount() * 100);
        $this->xmlPedidoPrazoEntregaDias = (string) '0';
        $this->xmlPedidoCanalVenda = (string) 'Loja Virtual';
    }

    /**
     * Queue a transaction for analysis
     *
     * @return array 
     */
    public function enfileirar() {
        
        if(!Mage::getStoreConfig('allpago/fcontrol/active')){
            return false;
        }
        //Obtém e bloqueia o recurso
        $locker = $this->criaLock('fcontrol/locker', 'fcontrol_enfileirar');
        //Define as configurações
        $this->defineConfig();
        $log = Mage::getModel('allpago_mc/log');

        //Carrega todos os pedidos autorizados
        $gatewayPayments = Mage::getModel('allpago_mc/payment')->getCollection()->addStatusFilter('authorized');
        //Percorre todos os pedidos autorizados
        foreach ($gatewayPayments as $gatewayPayment) {
            //Muda o status da tabela do gateway
            $gatewayPayment->setStatus(Allpago_Fcontrol_Model_Orders::STATUS_FCONTROL);
            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
            $gatewayPayment->save();
            //Define os dados
            $fcontrolOrder = Mage::getModel('fcontrol/orders');
            $fcontrolOrder->setOrderId($gatewayPayment->getOrderId());
            $fcontrolOrder->setStatus(Allpago_Fcontrol_Model_Orders::STATUS_CREATED);
            $fcontrolOrder->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
            
            $fcontrolOrder->save();
        }

        //Carrega todos os pedidos do Fcontrol
        $fcontrolOrders = Mage::getModel('fcontrol/orders')->getCollection()
                ->addTimeFilter(Mage::getStoreConfig('allpago/allpago_mc/tempo_espera'))
                ->addStatusFilter('created');
        
        //Percorre todos os pedidos criados
        foreach ($fcontrolOrders as $fcontrolOrder) {
          
            //Pega os dados do Pedido
            $order = Mage::getModel('sales/order')->load($fcontrolOrder->getOrderId());
          
            //Se o número de tentativas for menor que o máximo
            if ($fcontrolOrder->getTries() < $this->qtd_tentativas && $fcontrolOrder->getStatus() != Allpago_Fcontrol_Model_Orders::STATUS_MAXTRIES) {
                
                //Define as informações do XML
                try {
                    $this->enfileirarData($order);
                    //Faz a requisição no webservice
                    $retornoGateway = self::soapCallFunction('enfileirar');

                    //Se ocorreu um erro
                    if ($retornoGateway['cod'] > 0) {
                        //Salva log
                        if(!$fcontrolOrder->getErrorCode()){
                            $log->add($fcontrolOrder->getOrderId(), 'Fcontrol', 'enfileirar()', Allpago_Fcontrol_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', serialize($retornoGateway));
                            //Define os novos dados
                            $fcontrolOrder->setErrorCode($retornoGateway['cod']);
                            $fcontrolOrder->setErrorMessage($retornoGateway['msg']);   
                            $fcontrolOrder->setTries($fcontrolOrder->getTries() + 1);                         
                        }
                    } else {
                        //Salva log
                        $log->add($fcontrolOrder->getOrderId(), 'Fcontrol', 'enfileirar()', Allpago_Fcontrol_Model_Orders::STATUS_QUEUED, 'O pedido foi enfileirado', serialize($retornoGateway));
                        //Define os novos dados
                        $fcontrolOrder->setErrorCode(null);
                        $fcontrolOrder->setErrorMessage(null);
                        $fcontrolOrder->setStatus(Allpago_Fcontrol_Model_Orders::STATUS_QUEUED);
                    }
                } catch (Exception $e) {
                    //Salva log
                    $log->add($fcontrolOrder->getOrderId(), 'Fcontrol', 'enfileirar()', Allpago_Fcontrol_Model_Orders::STATUS_ERROR, 'Ocorreu um erro', serialize($e->getMessage()));
                    
                    //Incrementa as tentativas
                    $fcontrolOrder->setTries($fcontrolOrder->getTries() + 1);                    
                }

                //Salva as informações padrão
                $fcontrolOrder->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $fcontrolOrder->save();
            }

            //Se o número de tentativas atingiu o máximo
            else { 
                
                //Salva log
                $log->add($fcontrolOrder->getOrderId(), 'Fcontrol', 'enfileirar()', Allpago_Fcontrol_Model_Orders::STATUS_MAXTRIES, 'Número máximo de tentativas atingido');
                //Limpa os dados sensíveis
                $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($fcontrolOrder->getOrderId(), 'order_id');
                $gatewayPayment->setInfo(null);
                $gatewayPayment->setAbandoned(1);
                $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                $gatewayPayment->save();
                //Define os dados da tabela auxiliar
                $fcontrolOrder->setStatus(Allpago_Fcontrol_Model_Orders::STATUS_MAXTRIES)->setAbandoned(1)->save();
                //Muda o status do pedido
                $order->cancel()->save();
            }
            // Limpa instancia do pedido atual.
            $order->clearInstance();
        }

        //Desaloca o recurso
        $locker->unlock();
    }

    /**
     * Capture all responses from analysis
     *
     * @return array
     */
    public function capturar() {
        
        if(!Mage::getStoreConfig('allpago/fcontrol/active')){
            return false;
        }        
        //Obtém e bloqueia o recurso
        $locker = $this->criaLock('fcontrol/locker', 'fcontrol_capturar');
        //Define as configurações
        $this->defineConfig();
        $log = Mage::getModel('allpago_mc/log');

        try {
            //Faz a requisição no webservice
            $retornoGateway = self::soapCallFunction('capturar');

            //Se existirem análises a serem tratadas
            if ((int) $retornoGateway['cod'] == 0) {

                //Inicializa o contador
                $fcontrolOrdersCont = 0;
                //Cria os objetos necessários
                $fcontrolOrder = Mage::getModel('fcontrol/orders');

                //Para cada análise encontrada
                foreach ($retornoGateway['analise'] as $analise) {

                    $this->xmlPedidoCodigoPedido = '';
                    //Se o limite de análises por vez não foi atingido
                    if ($fcontrolOrdersCont < $this->qtd_analises) {
                        //Incrementa o contador
                        $fcontrolOrdersCont++;

                        //Salva log
                        $log->add($analise['CodigoCompra'], 'Fcontrol', 'capturar()', (($analise['Status'] == 7) ? Allpago_Fcontrol_Model_Orders::STATUS_APPROVED : Allpago_Fcontrol_Model_Orders::STATUS_DENIED), (($analise['Status'] == 7) ? 'A análise foi aprovada' : 'A análise foi negada'), serialize($retornoGateway));

                        //Atualiza os dados na tabela
                        $fcontrolOrder->load($analise['CodigoCompra'], 'order_id');
                        $fcontrolOrder->setStatus(($analise['Status'] == 7) ? Allpago_Fcontrol_Model_Orders::STATUS_APPROVED : Allpago_Fcontrol_Model_Orders::STATUS_DENIED);
                        $fcontrolOrder->setStatusFcontrol($this->getFcontrolStatusMessage($analise['Status']));
                        $fcontrolOrder->setErrorCode(null);
                        $fcontrolOrder->setErrorMessage(null);
                        $fcontrolOrder->setInfo(serialize($analise));
                        $fcontrolOrder->setTries($fcontrolOrder->getTries() + 1);
                        $fcontrolOrder->setAbandoned(($analise['Status'] == 7) ? 0 : 1);                            
                        
                        //Define o código do pedido
                        $this->xmlPedidoCodigoPedido = (string) $analise['CodigoCompra'];

                        try {
                            //Confirma o recebimento da captura
                            $retornoGatewayConfirmacao = self::soapCallFunction('confirmar');
                            //Se o recebimento foi confirmado
                            if ((int) $retornoGatewayConfirmacao['cod'] == 0) {
                                //Se a análise foi aprovada
                                if ($fcontrolOrder->getStatus() == Allpago_Fcontrol_Model_Orders::STATUS_APPROVED) {
                                    //Atualiza os dados na tabela
                                    $fcontrolOrder->setStatus(Allpago_Fcontrol_Model_Orders::STATUS_CAPTUREPAYMENT);
                                }
                                //Se a análise foi negada
                                if ($fcontrolOrder->getStatus() == Allpago_Fcontrol_Model_Orders::STATUS_DENIED) {
                                    //Limpa os dados sensíveis
                                    $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($fcontrolOrder->getOrderId(), 'order_id');
                                    $gatewayPayment->setInfo(null);
                                    $gatewayPayment->setAbandoned(1);
                                    $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                                    $gatewayPayment->save();
                                    //Muda o status do pedido
                                    Mage::getModel('sales/order')->load($fcontrolOrder->getOrderId())->cancel()->save();
                                }
                            } else {
                                //Salva log
                                $log->add(null, 'Fcontrol', 'capturar()', Allpago_Fcontrol_Model_Orders::STATUS_ERROR, 'Erro ao confirmar captura', serialize($retornoGatewayConfirmacao));
                                //Atualiza os dados na tabela
                                $fcontrolOrder->setErrorCode($retornoGatewayConfirmacao['cod']);
                                $fcontrolOrder->setErrorMessage($retornoGatewayConfirmacao['msg']);
                                $fcontrolOrder->setStatus(Allpago_Fcontrol_Model_Orders::STATUS_ERROR);
                            }
                        } catch (Exception $e) {
                            //Salva log
                            $log->add($fcontrolOrder->getOrderId(), 'Fcontrol', 'capturar()', Allpago_Fcontrol_Model_Orders::STATUS_ERROR, 'Ocorreu uma exceção', serialize($retornoGatewayConfirmacao));
                        }

                        //Salva os dados na tabela
                        $fcontrolOrder->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
                        $fcontrolOrder->save();                            
                    }
                }
            }

            //Se não existirem análises a serem tratadas/erro
            else {
                //Salva log
                //$log->add(null, 'Fcontrol', 'capturar()', Allpago_Fcontrol_Model_Orders::STATUS_ERROR, 'Erro ao capturar', serialize($retornoGateway));
            }
        } catch (Exception $e) {
            //Salva log
            $log->add(null, 'Fcontrol', 'capturar()', Allpago_Fcontrol_Model_Orders::STATUS_ERROR, 'Ocorreu uma exceção', $e->getMessage());
        }

        //Desaloca o recurso
        $locker->unlock();
    }

    /**
     * Creates a instance of the SOAP Client
     *
     * @return SoapClient
     */
    private function soapConnect() {
        try {
            //Cria o cliente SOAP
            return new SoapClient($this->ws_url, array("trace" => 1, "encoding" => "UTF-8"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Call a SOAP function
     *
     * @param string $funcao
     * @return array
     */
    public function soapCallFunction($funcao) {
        try {
            //Cria o cliente SOAP
            $client = self::soapConnect();
            //Define qual função vai ser chamada e XML vai ser montado
            switch ($funcao) {
                case 'enfileirar':
                    $funcao = 'enfileirarTransacao4';
                    $xml = self::buildQueuingXml();
                    break;
                case 'capturar':
                    $funcao = 'capturarResultadosGeral2';
                    $xml = self::buildCaptureXml();
                    break;
                case 'confirmar':
                    $funcao = 'confirmarRetorno';
                    $xml = self::buildReturnConfirmationXml();
                    break;
                case 'status':
                    $funcao = 'alterarStatus2';
                    $xml = self::buildChangeStatusXml();
                    break;
            }
            
			//Zend_debug::dump($xml);
            //file_put_contents(Mage::getBaseUrl(),$xml);
            
            //Efetua a chamada
            return self::soapCallFunctionResult($funcao, $client->__soapCall($funcao, (array) $xml));
        } catch (Exception $e) {
            Mage::log($e,null,'fcontrol_erro_captura.log');
            throw $e;
        }
    }

    /**
     * Manipulates the results from soapCallFunction for a more user-friendly result
     *
     * @param string $funcao
     * @param stdClass $resultado
     * @return array
     */
    private function soapCallFunctionResult($funcao, $resultado) {
        try {
            //Define o string de resultado
            $funcao = $funcao . "Result";
            //Realiza as operações de acordo com o tipo de função
            switch ($funcao) {
                case 'capturarResultadosGeral2Result':
                    //Se não existirem resultados
                    if (!count((array) $resultado->$funcao)) {
                        //Monta o array de retorno
                        $retorno["cod"] = '999999';
                        $retorno["msg"] = 'Nenhuma análise encontrada para captura';
                    }
                    //Se existirem resultados
                    else {
                        //Zera o contador
                        $cont = 0;
                        //Define o caminho das análises
                        $analises = (!is_array($resultado->$funcao->WsAnalise2)) ? $resultado->$funcao : $resultado->$funcao->WsAnalise2;
                        //Monta o array de retorno
                        $retorno["cod"] = '0';
                        $retorno["msg"] = 'Foram capturadas ' . count($analises) . ' análises';
                        //Para cada análise
                        foreach ($analises as $analise) {
                            //Define os dados específicos da análise
                            $retorno['analise'][$cont]['CodigoCompra'] = $analise->CodigoCompra;
                            $retorno['analise'][$cont]['Status'] = $analise->Status;
                            $retorno['analise'][$cont]['CodigoMotivo'] = $analise->CodigoMotivo;
                            $retorno['analise'][$cont]['Comentario'] = $analise->Comentario;
                            $retorno['analise'][$cont]['Analista'] = $analise->Analista;
                            $retorno['analise'][$cont]['Email'] = $analise->Email;
                            $retorno['analise'][$cont]['Ramal'] = $analise->Ramal;
                            $retorno['analise'][$cont]['Telefone'] = $analise->Telefone;
                            $retorno['analise'][$cont]['OpiniaoFcontrol'] = $analise->OpiniaoFcontrol;
                            $retorno['analise'][$cont]['Score'] = $analise->Score;
                            //Incrementa o contador
                            $cont++;
                        }
                    }
                    break;
                default:
                    //Trata os dados recebidos
                    $resultado->$funcao->Mensagem = explode("|", $resultado->$funcao->Mensagem);
                    //Monta o array de retorno
                    $retorno["cod"] = (string) $resultado->$funcao->Mensagem[0];
                    $retorno["msg"] = (string) $resultado->$funcao->Mensagem[1];
                    break;
            }
            //Adiciona o resultado do gateway
            $retorno['gateway'] = (array) $resultado;
            //Retorna o array montado
            return $retorno;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Build the queue XML
     * WS function: enfileirarTransacao4
     *
     * @return stdClass
     */
    private function buildQueuingXml() {
        //Cria o objeto necessário
        $params = new stdClass();
        //Define os dados de login com o FControl
        $params->enfileirarTransacao4 = new stdClass();
        $params->enfileirarTransacao4->pedido = new stdClass();
        $params->enfileirarTransacao4->pedido->DadosUsuario = new stdClass();
        $params->enfileirarTransacao4->pedido->DadosUsuario->Login = $this->ws_login; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosUsuario->Senha = $this->ws_pass; //NOT NULL
        //Define os dados do comprador
        $params->enfileirarTransacao4->pedido->DadosComprador = new stdClass();
        $params->enfileirarTransacao4->pedido->DadosComprador->NomeComprador = $this->xmlCompradorNome; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->CpfCnpj = $this->xmlCompradorCpfCnpj; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Sexo = $this->xmlCompradorSexo;
        $params->enfileirarTransacao4->pedido->DadosComprador->DataNascimento = $this->xmlCompradorDataNascimento; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->DddTelefone = $this->xmlCompradorDddTelefone;
        $params->enfileirarTransacao4->pedido->DadosComprador->NumeroTelefone = $this->xmlCompradorNumeroTelefone;
        $params->enfileirarTransacao4->pedido->DadosComprador->DddTelefone2 = $this->xmlCompradorDddTelefone2;
        $params->enfileirarTransacao4->pedido->DadosComprador->NumeroTelefone2 = $this->xmlCompradorNumeroTelefone2;
        $params->enfileirarTransacao4->pedido->DadosComprador->DddCelular = $this->xmlCompradorDddCelular;
        $params->enfileirarTransacao4->pedido->DadosComprador->NumeroCelular = $this->xmlCompradorNumeroCelular;
        $params->enfileirarTransacao4->pedido->DadosComprador->IP = $this->xmlCompradorIP;
        $params->enfileirarTransacao4->pedido->DadosComprador->Email = $this->xmlCompradorEmail; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Senha = $this->xmlCompradorSenha;
        
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco = new stdClass();        
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Pais = $this->country; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Cep = $this->xmlCompradorEnderecoCep; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Rua = $this->xmlCompradorEnderecoRua; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Numero = $this->xmlCompradorEnderecoNumero; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Complemento = $this->xmlCompradorEnderecoComplemento;
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Bairro = $this->xmlCompradorEnderecoBairro;
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Cidade = $this->xmlCompradorEnderecoCidade; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosComprador->Endereco->Estado = $this->xmlCompradorEnderecoEstado; //NOT NULL
        //Define os dados de entrega
        $params->enfileirarTransacao4->pedido->DadosEntrega = new stdClass();        
        $params->enfileirarTransacao4->pedido->DadosEntrega->NomeEntrega = $this->xmlEntregaNome; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->CpfCnpj = $this->xmlEntregaCpfCnpj;
        $params->enfileirarTransacao4->pedido->DadosEntrega->Sexo = $this->xmlEntregaSexo;
        $params->enfileirarTransacao4->pedido->DadosEntrega->DataNascimento = $this->xmlEntregaDataNascimento;
        $params->enfileirarTransacao4->pedido->DadosEntrega->DddTelefone = $this->xmlEntregaDddTelefone; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->NumeroTelefone = $this->xmlEntregaNumeroTelefone; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->DddTelefone2 = $this->xmlEntregaDddTelefone2;
        $params->enfileirarTransacao4->pedido->DadosEntrega->NumeroTelefone2 = $this->xmlEntregaNumeroTelefone2;
        $params->enfileirarTransacao4->pedido->DadosEntrega->DddCelular = $this->xmlEntregaDddCelular;
        $params->enfileirarTransacao4->pedido->DadosEntrega->NumeroCelular = $this->xmlEntregaNumeroCelular;
        
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco = new stdClass(); 
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Pais = $this->country; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Cep = $this->xmlEntregaEnderecoCep; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Rua = $this->xmlEntregaEnderecoRua; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Numero = $this->xmlEntregaEnderecoNumero; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Complemento = $this->xmlEntregaEnderecoComplemento;
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Bairro = $this->xmlEntregaEnderecoBairro;
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Cidade = $this->xmlEntregaEnderecoCidade; //NOT NULL
        $params->enfileirarTransacao4->pedido->DadosEntrega->Endereco->Estado = $this->xmlEntregaEnderecoEstado; //NOT NULL
        //Dados do Pedido
        $params->enfileirarTransacao4->pedido->CodigoPedido = $this->xmlPedidoCodigoPedido; //NOT NULL
        $params->enfileirarTransacao4->pedido->DataCompra = $this->xmlPedidoDataCompra; //NOT NULL
        $params->enfileirarTransacao4->pedido->QuantidadeItensDistintos = $this->xmlPedidoQuantidadeItensDistintos; //NOT NULL
        $params->enfileirarTransacao4->pedido->QuantidadeTotalItens = $this->xmlPedidoQuantidadeTotalItens; //NOT NULL
        $params->enfileirarTransacao4->pedido->ValorTotalCompra = $this->xmlPedidoValorTotalCompra; //NOT NULL
        $params->enfileirarTransacao4->pedido->ValorTotalFrete = $this->xmlPedidoValorTotalFrete;
        $params->enfileirarTransacao4->pedido->PedidoDeTeste = $this->xmlPedidoPedidoDeTeste;
        $params->enfileirarTransacao4->pedido->PrazoEntregaDias = $this->xmlPedidoPrazoEntregaDias;
        $params->enfileirarTransacao4->pedido->FormaEntrega = $this->xmlPedidoFormaEntrega;
        $params->enfileirarTransacao4->pedido->Observacao = $this->xmlPedidoObservacao;
        $params->enfileirarTransacao4->pedido->CanalVenda = $this->xmlPedidoCanalVenda;
        //Dados dos Produtos e Pagamentos
        $params->enfileirarTransacao4->pedido->Produtos = new stdClass();        
        $params->enfileirarTransacao4->pedido->Produtos->WsProduto3 = $this->xmlPedidoProdutos;
        $params->enfileirarTransacao4->pedido->Pagamentos = new stdClass();        
        $params->enfileirarTransacao4->pedido->Pagamentos->WsPagamento2 = $this->xmlPedidoPagamentos;
        //Dados Extra
        $params->enfileirarTransacao4->pedido->DadosExtra = new stdClass();        
        $params->enfileirarTransacao4->pedido->DadosExtra->Extra1 = $this->xmlDadosExtra1;
        $params->enfileirarTransacao4->pedido->DadosExtra->Extra2 = $this->xmlDadosExtra2;
        $params->enfileirarTransacao4->pedido->DadosExtra->Extra3 = $this->xmlDadosExtra3;
        $params->enfileirarTransacao4->pedido->DadosExtra->Extra4 = $this->xmlDadosExtra4;
        //Retorna o array com os parâmetros
        return $params;
    }

    /**
     * Build the Capture XML
     * WS function: capturarResultadosGeral2
     *
     * @return stdClass
     */
    private function buildCaptureXml() {
        //Cria o objeto necessário
        $params = new stdClass();
        //Define os dados de login com o FControl
        $params->capturarResultadosGeral2   = new stdClass();
        $params->capturarResultadosGeral2->login = $this->ws_login;
        $params->capturarResultadosGeral2->senha = $this->ws_pass;
        //Retorna o array com os parâmetros
        return $params;
    }

    /**
     * Build the Return Confirmation XML
     * WS function: confirmarRetorno
     *
     * @return stdClass
     */
    private function buildReturnConfirmationXml() {
        //Cria o objeto necessário
        $params = new stdClass();
        //Define os dados de login com o FControl
        $params->confirmarRetorno = new stdClass();
        $params->confirmarRetorno->login = $this->ws_login;
        $params->confirmarRetorno->senha = $this->ws_pass;
        //Dados do pedido
        $params->confirmarRetorno->codigoPedido = $this->xmlPedidoCodigoPedido;
        //Retorna o array com os parâmetros
        return $params;
    }

    /**
     * Build the Change Status XML
     * WS function: alterarStatus2
     *
     * Possible Status Codes:
     * 2 - Enviado
     * 3 - Cancelado
     * 6 - Cancelado por Suspeita
     * 7 - Aprovada
     * 10 - Fraude Confirmada
     * 13 - Não aprovado pela operadora do cartão
     *
     * @return stdClass
     */
    private function buildChangeStatusXml() {
        //Cria o objeto necessário
        $params = new stdClass();
        //Define os dados de login com o FControl
        $params->alterarStatus2 = new stdClass();
        $params->alterarStatus2->login = $this->ws_login;
        $params->alterarStatus2->senha = $this->ws_pass;
        //Dados do pedido
        $params->alterarStatus2->codigoPedido = $this->xmlPedidoCodigoPedido;
        //Dados do status
        $params->alterarStatus2->status = $this->xmlPedidoStatusCodigo; //'7';
        $params->alterarStatus2->codigoMotivo = $this->xmlPedidoMotivoCodigo;
        $params->alterarStatus2->comentario = $this->xmlPedidoComentario;
        $params->alterarStatus2->compartilharComentario = $this->xmlPedidoCompartilharComentario; //'false';
        //Retorna o array com os parâmetros
        return $params;
    }

}
