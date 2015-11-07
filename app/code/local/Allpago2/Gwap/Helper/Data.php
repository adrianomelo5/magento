<?php
/**
 * Allpago - Gwap Payment Module
 *
 * @title      Magento -> Custom Payment Module for Gwap
 * @category   Payment Gateway
 * @package    Allpago_Gwap
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_order;
    private $_config;
    private $_auth;
    
    public function isOnepageActive() {
        return Mage::getStoreConfig('onepagecheckout/general/enabled');
    }
    
    public  function setOrder($order){
        $this->_order = $order;
        return $this;
    } 
    
    public function getOrder(){
        return $this->_order;
    }
    
    public function getConfig() {
        if( $this->_config )
               return $this->_config;
          
        $this->_config = new Varien_Object(Mage::getStoreConfig('payment/gwap_boleto' ));
        return $this->_config;
    }
    public function getAuthConfig() {
        if( $this->_auth )
               return $this->_auth;
       
        $this->_auth = new Varien_Object(Mage::getStoreConfig('payment/gwap_auth'));
        return $this->_auth;
    }
    
    public function prepareData( $type ){
        
        $order = $this->getOrder();
        $config = $this->getConfig();
        $auth = $this->getAuthConfig();
        
        $parameters = array();
        
         //prepare parameters
        $parameters['RESPONSE.VERSION'] = '1.0';
        $parameters['TRANSACTION.MODE'] = $auth->getAmbiente(); #####PEGAR AMBIENTE######
        $parameters['TRANSACTION.RESPONSE'] = 'SYNC';
        $parameters['SECURITY.SENDER'] = trim($auth->getSecuritySender());
       
        $transaction_type = 'transaction_channel_'.strtolower($type);

        $parameters['TRANSACTION.CHANNEL'] = trim($config->getData($transaction_type)); #####PEGAR CANAL######
        $parameters['USER.LOGIN'] = trim($auth->getUserLogin());
        $parameters['USER.PWD'] = strval(Mage::helper("core")->decrypt($auth->getUserPwd()));
        
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $order->getIncrementId();
         
        $parameters['PAYMENT.CODE'] = 'PP.PA';
        $parameters['PRESENTATION.AMOUNT'] = number_format($order->getGrandTotal(), 2, '.', '');
        $parameters['PRESENTATION.CURRENCY'] = "BRL";
        
        $street = utf8_decode($order->getBillingAddress()->getStreet(1));
        if (strlen($street) < 5) {
            $street = 'Rua ' . utf8_decode($order->getBillingAddress()->getStreet(1));
        }

        $parameters['ADDRESS.STREET'] = $street;
        $parameters['ADDRESS.ZIP'] = str_replace('-', '', utf8_decode($order->getBillingAddress()->getPostcode()));
        
        $city = $order->getBillingAddress()->getCity();    
        
        $parameters['ADDRESS.CITY'] = utf8_decode($city);
        $parameters['ADDRESS.COUNTRY'] = utf8_decode($order->getBillingAddress()->getCountryId());
        $parameters['ADDRESS.STATE'] = $order->getBillingAddress()->getRegionId()  
                                            ? Mage::getModel('directory/region')->load( $order->getBillingAddress()->getRegionId() )->getCode()
                                            :  $order->getBillingAddress()->getRegion();
                                     
        $parameters['CONTACT.EMAIL'] = trim($order->getBillingAddress()->getEmail())
                                       ? trim(utf8_decode($order->getBillingAddress()->getEmail())) : trim(utf8_decode($order->getCustomerEmail()));

        $parameters['NAME.GIVEN'] = utf8_decode($order->getBillingAddress()->getFirstname());
        $parameters['NAME.FAMILY'] = utf8_decode($order->getBillingAddress()->getLastname());
        
        $vencimento = $config->getVencimento();
        if( is_numeric($vencimento) && $vencimento > 0 ){
            $due_date = Mage::getModel('core/date')->timestamp( '+'.$vencimento.' days' );
        }else{
            $due_date = Mage::getModel('core/date')->timestamp( '+1 day' );
        }
        
        $cpf = $order->getCustomerTaxvat();
        if(!$cpf){
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $customerDocs = explode(",", Mage::getStoreConfig('allpago/gwap_boleto/campo_documento'));
            $cpf = null;

            foreach ($customerDocs as $customerDoc) {
                $metodo = 'get' . ucfirst($customerDoc);
                if (!$cpf && $customer->$metodo()) {
                    $cpf = (string) preg_replace('/[^0-9]/', '', $customer->$metodo());
                }
            }        
        }

        if(!$cpf){
            $cpf = '00000000000';
        }        
        
        switch ($type){
            
            case 'BRADESCO':
                 
                if( is_numeric($vencimento) && $vencimento > 3 ){
                    $due_date = Mage::getModel('core/date')->timestamp( '+3 days' );
                }
                if( $config->getInstrucoes() ){
                    
                    $instrucoes = explode( PHP_EOL, $config->getInstrucoes() ); 
                    foreach ( $instrucoes as $key => $inst){
                        $parameters['CRITERION.BRADESCO_instrucao'.($key+1)]  = $inst;
                    }
                }

                $parameters['CRITERION.BRADESCO_numeropedido']  = $order->getIncrementId();
                $parameters['CRITERION.BRADESCO_datavencimento']  = date( 'd/m/Y', $due_date );
                $parameters['CRITERION.BRADESCO_cpfsacado']  = (string) str_replace(array('.','-',' '), array('', '', ''), $cpf);
                
                break;
            
            case 'ITAU':

                $parameters['CRITERION.BOLETO_Due_date']  =  date( 'dmY', $due_date );
                $parameters['CRITERION.BOLETO_Codeenrollment']  = '01'; 
                $parameters['CRITERION.BOLETO_Numberenrollment']  = (string) str_pad( str_replace(array('.','-',' '), array('', '', ''), $cpf), 14, "0", STR_PAD_LEFT);
                $parameters['CRITERION.BOLETO_BairroSacado']  = $order->getBillingAddress()->getStreet(4);
                
                break;
        }

        return $parameters;        
    }
    
    public function getBoletoUrl( $orderId ){
        $order =  Mage::getModel('sales/order')->loadByIncrementId($orderId);
        
        $store = Mage::getModel('core/store')->load($order->getStoreId());
        return $store->getUrl('allpago_gwap/imprimir/boleto', array('id'=>$order->getId(), 'ci'=>$order->getCustomerId()));
    }
    
    public function sendCancelEmail( $order ){  
        
        if( is_numeric(Mage::getStoreConfig('allpago/gwap_cc/template_cancelamento', Mage::app()->getStore())) ){
            
            $emailTemplate = Mage::getModel( 'core/email_template' );
            /* @var $mailTemplate Mage_Core_Model_Email_Template */

            $translate  = Mage::getSingleton( 'core/translate' );

            $customer = true;
            //fetch sender data from Adminend > System > Configuration > Store Email Addresses > General Contact
            $from_email = Mage::getStoreConfig( 'trans_email/ident_general/email' ); //fetch sender email
            $from_name = Mage::getStoreConfig( 'trans_email/ident_general/name' ); //fetch sender name
            
            $email = $order->getCustomerEmail();
            $name = $order->getCustomerName();
            $vars = array( 'customer'=>$customer,
                'incrementid' => $order->getIncrementId() );            
            
            $storeId = Mage::app()->getStore()->getId();
            try {
                
                $emailTemplate->setReplyTo($email)
                        ->sendTransactional(
                              Mage::getStoreConfig('allpago/gwap_cc/template_cancelamento', Mage::app()->getStore()),
                              array('name' => $from_name,
                                    'email' => $from_email),
                                    $email,
                                    $name, 
                                    $vars, 
                                    $storeId);
                
                if ( !$emailTemplate->getSentSuccess() ) {
                    throw new Exception();
                }
                $translate->setTranslateInline( true );   
                
            } catch (Exception $e) {
                Mage::logException('Allpago (sendCancelEmail) - Erro:'.$e->getMessage(),null,'allpago.log');
            }
            
        }
       
    }
    
    public function getRequestURL() {
        $auth = $this->getAuthConfig();
        if ($auth->getAmbiente() == 'LIVE') {
            return 'https://ctpe.net/frontend/payment.prc';
        } else { //'CONNECTOR_TEST'
            return 'https://test.ctpe.net/frontend/payment.prc';
        }
    }    
    
    public function buildPostString($parameters) {
        $result = '';
        foreach (array_keys($parameters) AS $key) {
            if (!isset($$key)) {
                $$key = '';
            }
            if (!isset($result)) {
                $result = '';
            }
            $$key .= $parameters[$key];
            $$key = urlencode($$key);
            $$key .= "&";
            if (!stristr($key, 'cpf') && !stristr($key, 'number_of_installments')) {
                $var = strtoupper($key);
            } else {
                $var = $key;
            }
            $value = $$key;
            $result .= "$var=$value";
        }
        return stripslashes($result);
    }    
    
    public function makeCurlRequest($url, $postString) {
        $cpt = curl_init();
        curl_setopt($cpt, CURLOPT_URL, $url);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($cpt, CURLOPT_USERAGENT, "php ctpepost");
        curl_setopt($cpt, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cpt, CURLOPT_POST, 1);
        curl_setopt($cpt, CURLOPT_POSTFIELDS, $postString);
       //curl_setopt($cpt, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"));

        $curlresultURL = '';
        $curlresultURL = curl_exec($cpt);
        $curlerror = curl_error($cpt);
        $curlinfo = curl_getinfo($cpt);
        curl_close($cpt);

        $r_arr = explode("&", $curlresultURL);
        foreach ($r_arr as $buf) {
            $temp = urldecode($buf);
            $temp = explode("=", $temp, 2);
            if ($temp[0] && $temp[1]) {
                $postatt = $temp[0];
                $postvar = $temp[1];
                $returnvalue[$postatt] = $postvar;
            }
        }
        //$resultCode = explode('.', $returnvalue['PROCESSING.CODE']);
        //$this->_resultCode = $resultCode[2];

        return $returnvalue;
    }    
    
    /**
     * Fazer pre-autorização quando:<br/>
     * -Fcontrol ativado e valor do pedido menor que a configuracao vlr_minimo no Fcontrol.<br/>
     * -Ou Clearsale ativado e valor do pedido menor que a configuracao vlr_minimo no Clearsale.<br/>
     */
    public function getAntifraude($order) {
        return Mage::getStoreConfig('allpago/fcontrol/active') && Mage::getStoreConfig('allpago/fcontrol/vlr_minimo') > $order->getGrandTotal()
                    || Mage::getStoreConfig('allpago/clearsale/active') && Mage::getStoreConfig('allpago/clearsale/vlr_minimo') > $order->getGrandTotal();
    }    
    
    public function reversalOrRefund($incrementId,$referenceId,$paymentCode,$ccType) {
        
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $incrementId;
        $parameters['IDENTIFICATION.REFERENCEID'] = $referenceId;
        $parameters = array_merge($parameters, $this->prepareCommonParameters($ccType));
        $parameters['PAYMENT.CODE'] = 'CC.'.$paymentCode;
        
        $url = $this->getRequestURL();
        $postString = $this->buildPostString($parameters);
        $response = $this->makeCurlRequest($url, $postString);

        return $response;        
    } 
    
    public function failureRedirect($errorMsg) {
        $block = Mage::app()->getLayout()->getMessagesBlock();
        $block->addError('Transação não autorizada  (' . $errorMsg . ')');
        Mage::app()
                ->getResponse()
                ->setRedirect(Mage::getUrl('allpago_gwap/checkout/failure'));
        Mage::app()
                ->getResponse()
                ->sendResponse();
        exit;
    }     
    
    public function cancelOrder($order, $errorMsg) {
        $log = Mage::getModel('allpago_mc/log');
        $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($order->getId(), 'order_id');
        if(!$gatewayPayment->getTries()){
            $qtd_tentativas = $gatewayPayment->getTries()+1;
        }
        $this->qtd_tentativas = Mage::getStoreConfig('allpago/allpago_mc/qtd_tentativas') ? Mage::getStoreConfig('allpago/allpago_mc/qtd_tentativas') : $this->qtd_tentativas;
        if ($qtd_tentativas >= $this->qtd_tentativas) {
            $log->add($order->getId(), '+ Conversao', 'authorize()', 'error', 'Ocorreu um erro na autorização instantânea', $errorMsg);
            $gatewayPayment->setInfo(null);
            if(!Mage::getStoreConfig('payment/gwap_cc/cancelamento')){
                $gatewayPayment->setStatus(Allpago_Mc_Model_Mc::STATUS_MAXTRIES);
            }else{
                $order->cancel()->save();                
                $gatewayPayment->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
                Mage::helper('gwap')->sendCancelEmail($order);
            }
            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        } else {
            $log->add($order->getId(), '+ Conversao', 'authorize()', 'error', 'Ocorreu um erro na autorização instantânea', $errorMsg);
            $gatewayPayment->setTries($gatewayPayment->getTries() + 1);
            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        }
        $gatewayPayment->save();
    }    
    
    /**
     *  Regex validations
     * 
     * @return string 
     */
    public function getVerificationRegEx() {
        $verificationExpList = array(
            'VISA' => '/^[0-9]{3}$/',
            'MASTER' => '/^[0-9]{3}$/',
            'ELO' => '/^[0-9]{3,4}$/',
            'AMEX' => '/^[0-9]{4}$/',
            'DISCOVER' => '/^[0-9]{3,4}$/',
            'DINERS' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{3}$/',
            'HIPERCARD' => '/^[0-9]{3}$/',
            'CABAL' => '/^[0-9]{3}$/',            
        );
        return $verificationExpList;
    }

    public function OtherCcType($type) {
        $typeArray = array('ELO','HIPERCARD','CABAL');
        return in_array($type, $typeArray);
    }   
    

    public function getUserLogin($config) {
        return trim($config->getUserLogin());
    }

    public function getUserPassword($config) {
        return strval(Mage::helper("core")->decrypt($config->getUserPwd()));
    }   
    
    public function getSecuritySender($config) {
        return trim($config->getSecuritySender());
    }    
    
}