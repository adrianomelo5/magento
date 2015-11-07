<?php

//require_once 'Zend/Log.php';

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
class Allpago_Gwap_Model_Methods_2cc extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'gwap_2cc';
    protected $_formBlockType = 'gwap/form_2cc';
    protected $_infoBlockType = 'gwap/info_2cc';
    protected $_paymentMethod = 'cc';
    
    protected $_canSaveCc = false;
    protected $_canCapture = true;
    protected $_isInitializeNeeded = true;
    
    protected $_resultCode = '';
    protected $_TID = '';
    protected $_cc = '';    
    protected $_cc2 = ''; 
    protected $_gwap;
    protected $_rg = ''; 
    //private   $qtd_tentativas = 1;    
    
    
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data) {

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        
        $info->setCcType($data->getData('gwap_2cc_type'))
                ->setCcOwner($data->getData('gwap_2cc_owner'))
                ->setCcLast4(substr($data->getData('gwap_2cc_number'), -4))
                ->setCcNumber(preg_replace("/[^a-zA-Z0-9\s]/", "", $data->getData('gwap_2cc_number')))
                ->setCcCid(preg_replace("/[^a-zA-Z0-9\s]/", "", $data->getData('gwap_2cc_cid')))    
                ->setCcExpMonth($data->getData('gwap_2cc_exp_month'))
                ->setCcExpYear($data->getData('gwap_2cc_exp_year'))
                ->setCcSsIssue($data->getData('gwap_2cc_ss_issue'))
                ->setCcParcelas($data->getData('gwap_2cc_parcelas'))             
                ->setAdditionalInformation('gwapCcAmount', str_replace(',','.',str_replace('.','',str_replace('R$ ','',$data->getData('gwap_2cc_amount')))))
                
                 /*Second*/
                ->setAdditionalInformation('gwapCcType2', $data->getData('gwap_2cc_type2'))
                ->setAdditionalInformation('gwapCcOwner2', $data->getData('gwap_2cc_owner2'))
                ->setAdditionalInformation('gwapCcLast4_2', substr($data->getData('gwap_2cc_number2'),-4))
                ->setAdditionalInformation('gwapCcNumber2',preg_replace("/[^a-zA-Z0-9\s]/", "", $data->getData('gwap_2cc_number2')))
                ->setAdditionalInformation('gwapCcCid2',preg_replace("/[^a-zA-Z0-9\s]/", "", $data->getData('gwap_2cc_cid2')))                 
                ->setAdditionalInformation('gwapCcExpMonth2', $data->getData('gwap_2cc_exp_month2'))
                ->setAdditionalInformation('gwapCcExpYear2', $data->getData('gwap_2cc_exp_year2'))
                ->setAdditionalInformation('gwapCcParcelas2', $data->getData('gwap_2cc_parcelas2'));
                
        //Mage::log($info->getCcNumber().' - '.$info->getCcType().' - '.$info->getCcType().' - '.$info->getCcExpYear().' - '.$info->getCcExpMonth(),null,'validation.log');                
        
        if(Mage::getStoreConfig('allpago/clearsale/active')){
             $info->setAdditionalInformation('GwapSessionId', $data->getGwapSessionId());
        }       
        
        return $this;
    }

    public function prepareSave() {
        parent::prepareSave();
    }    
    
    /**
     * Check whether there are CC types set in configuration
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (is_null($quote)) {
            return false;
        }
        if (!Mage::getStoreConfig('payment/gwap_2cc/active')) {
            return false;
        }
        return true;
    }    
    
    /**
     *  get Gwap system configuration
     * 
     * @return Varien_Object 
     */
    public function getConfig() {
        return new Varien_Object(Mage::getStoreConfig('payment/gwap_cc'));
    }

    /**
     *  get Gwap auth system configuration
     * 
     * @return Varien_Object 
     */
    public function getAuthConfig() {
        return new Varien_Object(Mage::getStoreConfig('payment/gwap_auth'));
    }

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $data = Mage::helper('gwap')->getGwapPaymentData($payment);
        $order = $payment->getOrder();   
        
        $mGwap = Mage::getModel('gwap/order');
        $mGwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CREATED);
        $mGwap->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        if (!Mage::getStoreConfig('payment/gwap_cc/tipo_autorizacao')) {
            $mGwap->setInfo(Mage::helper('core')->encrypt(serialize($data->toArray())));
        }
        $mGwap->setType(Mage::getStoreConfig('payment/gwap_2cc/mc_type'));
        $mGwap->setCcType($data->getCcType());
        $mGwap->setCcType2($data->getCcType2());
        $mGwap->setOrderId($order->getId());        
        $mGwap->save();
        
        $this->_gwap = $mGwap;
        
        if (Mage::getStoreConfig('payment/gwap_cc/tipo_autorizacao')) {
            $this->authorizeNow($order, $data->toArray());
        } else {
            $this->authorize($payment,$order->getGrandTotal());
        }        
                
    }    
    
    
    /**
     * Authorize
     *
     * @param   Varien_Object $orderPayment
     * @param float $amount
     * @return  Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount) {

        $order = $payment->getOrder();
        $orderId = $order->getId();
        $gwap = Mage::getModel('gwap/order')->load($orderId, 'order_id');
        if($this->_cc){
            $cc = new Varien_Object($this->_cc);
        }else{
            $cc = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwap->getInfo())));
        }

        $parameters1 = $this->prepareAuthenticationRequestParameters1($order, $cc, 1);
        $parameters2 = $this->prepareAuthenticationRequestParameters2($order, $cc, 2);
        
        $url = Mage::helper('gwap')->getRequestURL();

        // Se true, não efetua PA
        if ($this->getConfig()->getAcao()) { 
            //Autorização instantânea
            if($this->_cc){
                $this->_cc = Mage::helper('core')->encrypt(serialize($parameters1));                  
                $this->_cc2 = Mage::helper('core')->encrypt(serialize($parameters2));
            }else{
                $gwap->clearInstance();
                $gwap = Mage::getModel('gwap/order')->load($orderId, 'order_id');                
                $gwap->setInfo(Mage::helper('core')->encrypt(serialize($parameters1)));
                $gwap->setInfo2(Mage::helper('core')->encrypt(serialize($parameters2)));
                $gwap->save();
            }
            return $this;
        }
        
        $postString1 = Mage::helper('gwap')->buildPostString($parameters1);
        $postString2 = Mage::helper('gwap')->buildPostString($parameters2);
        
        $response1 = Mage::helper('gwap')->makeCurlRequest($url, $postString1);
        $response2 = Mage::helper('gwap')->makeCurlRequest($url, $postString2);
        
        $resultCode1 = explode('.', $response1['PROCESSING.CODE']);
        $resultCode1 = $resultCode1[2];
        
        $resultCode2 = explode('.', $response2['PROCESSING.CODE']);
        $resultCode2 = $resultCode2[2];        
        
        if ($resultCode1 != '90') {
            Mage::throwException($cc->getCcType().': Payment code: ' . $response1['PAYMENT.CODE'] . ' (' . $response1['PROCESSING.REASON'] . ' - ' . $response1['PROCESSING.RETURN'] . ')');
        }

        if ($resultCode2 != '90') {
            $errorMsg = $cc->getCcType2().': Payment code: ' . $response2['PAYMENT.CODE'] . ' (' . $response2['PROCESSING.REASON'] . ' - ' . $response2['PROCESSING.RETURN'] . ')';
                    
            // Em caso de erro, estornar primeiro cartão
            $removeResult = $this->removeCreditAuto($payment->getOrder(),$cc->getCcAmount(),$response1['IDENTIFICATION.UNIQUEID'],$cc->getCcType(),1);           
            Mage::throwException($errorMsg.'<br/>'.$cc->getCcType().': '.$removeResult);
        }        
        
        // prepare parameters to capture after Pre Authorize success            
        $captureParams1 = $this->prepareCaptureRequestParameters($response1,$cc,1);
        $captureParams2 = $this->prepareCaptureRequestParameters($response2,$cc,2);
        
        // Autorização instantânea
        if($this->_cc){ 
            $this->_cc = Mage::helper('core')->encrypt(serialize($captureParams1));
            $this->_cc2 = Mage::helper('core')->encrypt(serialize($captureParams2));   
        }
        
        if(Mage::getStoreConfig('allpago/clearsale/active')){
            $gwap->setClearsaleInfo(Mage::helper('core')->encrypt(serialize($cc)));  
        }
        $gwap->setInfo(Mage::helper('core')->encrypt(serialize($captureParams1)));
        $gwap->setInfo2(Mage::helper('core')->encrypt(serialize($captureParams2)));
        $gwap->save();         

        return $this;
    }

    /**
     * Capture
     *
     * @param   Varien_Object $orderPayment
     * @param float $amount
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount) {

        $log = Mage::getModel('allpago_mc/log');
        $gwap = Mage::getModel('gwap/order')->load($payment->getOrder()->getId(), 'order_id');

        if($gwap->getStatus() != Allpago_Gwap_Model_Order::STATUS_CAPTURED){
            // Processamento de pedidos não novos
            if ($gwap->getStatus() == Allpago_Gwap_Model_Order::STATUS_CREATED) {
                $this->authorize($payment, $amount);
                /**
                 * reload item
                 */
                $gwap->clearInstance();
                $gwap = Mage::getModel('gwap/order')->load($payment->getOrder()->getId(), 'order_id');

                $gwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT);
                $gwap->save();
            }

            $url = Mage::helper('gwap')->getRequestURL();

            if($this->_cc){
                $cc1 = new Varien_Object(unserialize(Mage::helper('core')->decrypt($this->_cc)));
                $cc2 = new Varien_Object(unserialize(Mage::helper('core')->decrypt($this->_cc2)));
            }else{
                $cc1 = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwap->getInfo())));            
                $cc2 = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwap->getInfo2())));
            }

            $parameters1 = $cc1->toArray();
            $parameters2 = $cc2->toArray();

            $postString1 = Mage::helper('gwap')->buildPostString($parameters1);
            $postString2 = Mage::helper('gwap')->buildPostString($parameters2);

            $response1 = Mage::helper('gwap')->makeCurlRequest($url, $postString1);
            $response2 = Mage::helper('gwap')->makeCurlRequest($url, $postString2);

            $resultCode1 = explode('.', $response1['PROCESSING.CODE']);
            $resultCode1 = $resultCode1[2];        

            $resultCode2 = explode('.', $response2['PROCESSING.CODE']);
            $resultCode2 = $resultCode2[2];                

            if ($resultCode1 != '90') {
                $errorMsg = $gwap->getCcType().': Payment code: ' . $response1['PAYMENT.CODE'] . ' (' . $response1['PROCESSING.REASON'] . ' - ' . $response1['PROCESSING.RETURN'] . ')';
                //Captura manual ativada
                if(Mage::getStoreConfig('payment/gwap_cc/captura')){
                    $log->add($gwap->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_ERROR, 'Ocorreu um erro', $errorMsg);
                }
                Mage::throwException($errorMsg);
            }
            if ($resultCode2 != '90') {
                $errorMsg = $gwap->getCcType2().': Payment code: ' . $response2['PAYMENT.CODE'] . ' (' . $response2['PROCESSING.REASON'] . ' - ' . $response2['PROCESSING.RETURN'] . ')';
                //Captura manual ativada
                if(Mage::getStoreConfig('payment/gwap_cc/captura')){
                    $log->add($gwap->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_ERROR, 'Ocorreu um erro', $errorMsg);
                }
                // Em caso de erro, estornar primeiro cartão            
                $removeResult = $this->removeCreditAuto($payment->getOrder(),$parameters1['PRESENTATION.AMOUNT'],$response1['IDENTIFICATION.UNIQUEID'],$gwap->getCcType(),1);           
                Mage::throwException($errorMsg.'<br/>'.$gwap->getCcType().': '.$removeResult);                        
            }        

            $log->add($gwap->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_CAPTURED, 'Pagamento capturado');

            //Salva UNIQUEID da captura para possibilitar estorno
            $gwap->setInfo(serialize(array('UNIQUEID'=>$response1['IDENTIFICATION.UNIQUEID'])));
            $gwap->setInfo2(serialize(array('UNIQUEID'=>$response2['IDENTIFICATION.UNIQUEID'])));
            $gwap->setCaptureResult(serialize($this->getTID($resultCode1))); 
            $gwap->setCaptureResult2(serialize($this->getTID2($resultCode2))); 

            //Completar processo do pedido para o caso do RG gerar erro.
            $gwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CAPTURED);
            $gwap->setErrorCode(null);
            $gwap->setErrorMessage(null);
            $gwap->setTries(0);
            $gwap->setAbandoned(0);
            $gwap->save();
        }
        return $this;
    }
    
    public function authorizeNow($order, $cc) {

        $log = Mage::getModel('allpago_mc/log');
        Mage::getSingleton('core/session')->setAllpagoNewOrderSent(1);
        
        try {
            $this->_cc = '';
            $this->_cc = $cc;
            $this->authorize($order->getPayment(), $order->getGrandTotal());

            //Salva log
            if (!Mage::getStoreConfig('payment/gwap_cc/acao')) {
                $log->add($order->getId(), 'Payment', 'authorize()', Allpago_Mc_Model_Mc::STATUS_AUTHORIZED, 'Pagamento autorizado (pendente de captura)');
            }
            $gwap = $this->_gwap;
            //Se for Pré-autorização ou Antifraude estiver ligado Status = authorized
            $gwap->setStatus((Mage::helper('gwap')->getAntifraude($order) ? Allpago_Mc_Model_Mc::STATUS_AUTHORIZED : Allpago_Mc_Model_Mc::STATUS_CAPTUREPAYMENT));
            $gwap->setErrorCode(null);
            $gwap->setErrorMessage(null);
            $time = Mage::getStoreConfig('allpago/allpago_mc/tempo_espera');
            $gwap->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s", strtotime("-{$time} hours")));
            $gwap->setTries(0);
            $gwap->setAbandoned(0);
            $gwap->save();

            //DB
            if ($this->getConfig()->getAcao()) { // STATUS_CAPTUREPAYMENT

                try {
                    $this->capture($order->getPayment(), $order->getGrandTotal());

                    $invoice = $order->prepareInvoice()->register();
                    $invoice->setEmailSent(false);
                    $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
                    $invoice->getOrder()->setTotalPaid($order->getGrandTotal());
                    $invoice->getOrder()->setBaseTotalPaid($order->getBaseGrandTotal());
                    $invoice->getOrder()->setCustomerNoteNotify(true);
                    $invoice->getOrder()->setIsInProcess(true);
                    Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
                    $invoice->sendEmail(true, 'Pedido realizado com sucesso');

                    //Altera o status do pedido
                    //$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                    Mage::getSingleton('core/session')->setAllpagoNewOrderSent();
                    
                    if(Mage::getStoreConfig('allpago/clearsale/active')
                            && Mage::getStoreConfig('allpago/clearsale/produto') == 'clearid' 
                                && Mage::getStoreConfig('allpago/clearsale/clearid_questionario')){
                        return true;
                    }                    
                    
                    $block = Mage::app()->getLayout()->getMessagesBlock();
                    $block->addSuccess('Transação autorizada com sucesso');
                    return true;
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->setAllpagoNewOrderSent();
                    Mage::helper('gwap')->cancelOrder($order, $e->getMessage());
                    Mage::helper('gwap')->failureRedirect($e->getMessage());
                    return false;
                }
            } else {
                $order->sendNewOrderEmail();
                Mage::getSingleton('core/session')->setAllpagoNewOrderSent();
                
                if(Mage::getStoreConfig('allpago/clearsale/active')
                        && Mage::getStoreConfig('allpago/clearsale/produto') == 'clearid' 
                            && Mage::getStoreConfig('allpago/clearsale/clearid_questionario')){
                    return true;
                }                
                
                $block = Mage::app()->getLayout()->getMessagesBlock();
                $block->addSuccess('Transação autorizada com sucesso');
                return true;
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->setAllpagoNewOrderSent();
            Mage::helper('gwap')->cancelOrder($order, $e->getMessage());
            Mage::helper('gwap')->failureRedirect($e->getMessage());
            return false;
        }
    }
    
    public function reversalOrRefundAuto($incrementId,$amount,$referenceId,$paymentCode,$ccType,$ccPosition) {
        
        $response = array();
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $incrementId.'_'.$ccPosition.$paymentCode;
        $parameters['IDENTIFICATION.REFERENCEID'] = $referenceId;
        $parameters = array_merge($parameters, $this->prepareCommonParameters($ccType,$ccPosition));
        $parameters = array_merge($parameters, $this->preparePresentationParameters($amount,$ccPosition));
        $parameters['PAYMENT.CODE'] = 'CC.'.$paymentCode;
        var_dump($parameters);
        
        
        $url = Mage::helper('gwap')->getRequestURL();
        $postString = Mage::helper('gwap')->buildPostString($parameters);
        $response[] = Mage::helper('gwap')->makeCurlRequest($url, $postString);       

        return $response;        
    }    
    
    public function reversalOrRefund($incrementId,$referenceIds,$paymentCode,$ccTypes) {
        
        $response = array();
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $incrementId.'_1'.$paymentCode;
        $parameters['IDENTIFICATION.REFERENCEID'] = $referenceIds[0];
        $parameters = array_merge($parameters, $this->prepareCommonParameters($ccTypes[0],1));
        $parameters['PAYMENT.CODE'] = 'CC.'.$paymentCode;
        
        $url = Mage::helper('gwap')->getRequestURL();
        $postString = Mage::helper('gwap')->buildPostString($parameters);
        $response[] = Mage::helper('gwap')->makeCurlRequest($url, $postString);
        
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $incrementId.'_2'.$paymentCode;
        $parameters['IDENTIFICATION.REFERENCEID'] = $referenceIds[1];
        $parameters = array_merge($parameters, $this->prepareCommonParameters($ccTypes[1],2));
        $parameters['PAYMENT.CODE'] = 'CC.'.$paymentCode;
        
        $url = Mage::helper('gwap')->getRequestURL();
        $postString = Mage::helper('gwap')->buildPostString($parameters);
        $response[] = Mage::helper('gwap')->makeCurlRequest($url, $postString);        

        return $response;        
    }    
    
    public function removeCreditAuto($order,$amount1,$referenceId,$ccType,$ccPosition){
        
            $now       = date('d', Mage::getModel('core/date')->timestamp(time()));
            $createdAt = date('d', Mage::getModel('core/date')->timestamp($order->getCreatedAt()));
            $amount = $order->getGrandTotal() - $amount1;

            if ($now > $createdAt){
                $response = $this->reversalOrRefundAuto($order->getIncrementId(),$amount,$referenceId,'RF',$ccType,$ccPosition);  
            }else{
                $response = $this->reversalOrRefundAuto($order->getIncrementId(),$amount,$referenceId,'RV',$ccType,$ccPosition);  
            }
            $resultCode = explode('.', $response['PROCESSING.CODE']);
            $resultCode = $resultCode[2]; 
            
            if ($resultCode != '90') {
                return 'Payment code: ' . $response['PAYMENT.CODE'] . ' (' . $response['PROCESSING.REASON'] . ' - ' . $response['PROCESSING.RETURN'] . ')';
            }else{
                return 'Pagamento estornado';
            }
    }    
    
    public function removeCredit($order){
        
        $gwap = Mage::getModel('gwap/order')->load($order->getId(),'order_id');
        if($gwap->getStatus() == 'captured' 
                || ($gwap->getStatus() == 'authorized' && !$this->getConfig()->getAcao()) ){
         
            $identifications = array();
            $identification1 = unserialize($gwap->getInfo());
            $identification2 = unserialize($gwap->getInfo2());
            $identifications[] = $identification1['UNIQUEID'];
            $identifications[] = $identification2['UNIQUEID'];

            $ccTypes = array();
            $ccTypes[] = $gwap->getCcType();
            $ccTypes[] = $gwap->getCcType2();
            
            $now       = date('d', Mage::getModel('core/date')->timestamp(time()));
            $createdAt = date('d', Mage::getModel('core/date')->timestamp($order->getCreatedAt()));

            if ($now > $createdAt){
                $response = $this->reversalOrRefund($order->getIncrementId(),$identifications,'RF',$ccTypes);  
            }else{
                $response = $this->reversalOrRefund($order->getIncrementId(),$identifications,'RV',$ccTypes);  
            }

            $resultCode1 = explode('.', $response[0]['PROCESSING.CODE']);
            $resultCode1 = $resultCode1[2]; 
            if ($resultCode1 != '90') {
                Mage::throwException('Payment code: ' . $response[0]['PAYMENT.CODE'] . ' (' . $response[0]['PROCESSING.REASON'] . ' - ' . $response[0]['PROCESSING.RETURN'] . ')');                
            }
                
            $resultCode2 = explode('.', $response[1]['PROCESSING.CODE']);
            $resultCode2 = $resultCode2[2];
            if ($resultCode2 != '90') {
                Mage::throwException('Payment code: ' . $response[1]['PAYMENT.CODE'] . ' (' . $response[1]['PROCESSING.REASON'] . ' - ' . $response[1]['PROCESSING.RETURN'] . ')');
            }
            
        }else {
            Mage::throwException('Não é possível estornar um pedido ainda não autorizado ou capturado.');
        }        

    }    
    
    /**
     *  check if capture is available
     * 
     * @return bool
     */
    public function canCapture() {
        return true;
    }

    /**
     * Get gwap session namespace
     *
     * @return Allpago_Gwap_Model_Session
     */
    public function getSession() {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return $this->getCheckout()->getQuote();
    }

    private function prepareCommonParameters($ccType,$ccPosition) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();
        $parameters['RESPONSE.VERSION'] = '1.0';
        $parameters['TRANSACTION.MODE'] = $auth->getAmbiente();
        $parameters['TRANSACTION.RESPONSE'] = 'SYNC';
        $parameters['SECURITY.SENDER'] = Mage::helper('gwap')->getSecuritySender($auth);
        if($ccPosition<2){
            $parameters['TRANSACTION.CHANNEL'] = $this->getTransactionChannel1($config,$ccType);
        }else{
            $parameters['TRANSACTION.CHANNEL'] = $this->getTransactionChannel2($config,$ccType);
        }
        $parameters['USER.LOGIN'] = Mage::helper('gwap')->getUserLogin($auth);
        $parameters['USER.PWD'] = Mage::helper('gwap')->getUserPassword($auth);
        return $parameters;
    }   
    
    private function prepareAccountParameters1($cc) {
        $parameters['ACCOUNT.HOLDER'] = $cc->getCcOwner();
        $parameters['ACCOUNT.NUMBER'] = $cc->getCcNumber();
        $parameters['ACCOUNT.BRAND'] = $cc->getCcType();
        $parameters['ACCOUNT.EXPIRY_MONTH'] = $cc->getCcExpMonth();
        $parameters['ACCOUNT.EXPIRY_YEAR'] = $cc->getCcExpYear();
        $parameters['ACCOUNT.VERIFICATION'] = $cc->getCcCid();
        return $parameters;
    }
    
    private function prepareAccountParameters2($cc) {
        $parameters['ACCOUNT.HOLDER'] = $cc->getCcOwner2();
        $parameters['ACCOUNT.NUMBER'] = $cc->getCcNumber2();
        $parameters['ACCOUNT.BRAND'] = $cc->getCcType2();
        $parameters['ACCOUNT.EXPIRY_MONTH'] = $cc->getCcExpMonth2();
        $parameters['ACCOUNT.EXPIRY_YEAR'] = $cc->getCcExpYear2();
        $parameters['ACCOUNT.VERIFICATION'] = $cc->getCcCid2();
        return $parameters;
    }    

    private function prepareAddressParameters($order) {
        $parameters = array();
        $street = utf8_decode($order->getBillingAddress()->getStreet(1));
        if (strlen($street) < 5) {
            $street = 'Rua ' . utf8_decode($order->getBillingAddress()->getStreet(1));
        }
        $parameters['ADDRESS.STREET'] = $street;
        $parameters['ADDRESS.ZIP'] = str_replace('-', '', utf8_decode($order->getBillingAddress()->getPostcode()));
        $parameters['ADDRESS.CITY'] = utf8_decode($order->getBillingAddress()->getCity());
        $parameters['ADDRESS.COUNTRY'] = utf8_decode($order->getBillingAddress()->getCountryId());
        $parameters['ADDRESS.STATE'] = $order->getBillingAddress()->getRegion() ? Mage::getModel('directory/region')->load($order->getBillingAddress()->getRegionId())->getCode() : $order->getBillingAddress()->getRegion();
        return $parameters;
    }

    private function prepareAuthenticationRequestParameters1($order, $data, $count) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $order->getIncrementId().'_1';
        $parameters = array_merge($parameters, $this->prepareCommonParameters($data->getCcType(),$count));
        $parameters = array_merge($parameters, $this->prepareAccountParameters1($data));
        $parameters = array_merge($parameters, $this->prepareAddressParameters($order));
        $parameters = array_merge($parameters, $this->preparePresentationParameters(number_format($data->getCcAmount(), 2, '.', '')));
        if ($data->getCcParcelas() > 1) {
            $parameters['CRITERION.CUSTOM_number_of_installments'] = $data->getCcParcelas();
        }
        $parameters['PAYMENT.CODE'] = $this->getConfig()->getAcao() ? 'CC.DB' : 'CC.PA';
        $parameters['CONTACT.EMAIL'] = trim($order->getBillingAddress()->getEmail()) ? trim(utf8_decode($order->getBillingAddress()->getEmail())) : trim(utf8_decode($order->getCustomerEmail()));
        $parameters['NAME.GIVEN'] = utf8_decode($order->getBillingAddress()->getFirstname());
        $parameters['NAME.FAMILY'] = utf8_decode($order->getBillingAddress()->getLastname());
        return $parameters;
    }
    
    private function prepareAuthenticationRequestParameters2($order, $data, $count) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $order->getIncrementId().'_2';
        $parameters = array_merge($parameters, $this->prepareCommonParameters($data->getCcType2(), $count));
        $parameters = array_merge($parameters, $this->prepareAccountParameters2($data));
        $parameters = array_merge($parameters, $this->prepareAddressParameters($order));
        $parameters = array_merge($parameters, $this->preparePresentationParameters(number_format($order->getGrandTotal() - $data->getCcAmount(), 2, '.', '')));
        if ($data->getCcParcelas2() > 1) {
            $parameters['CRITERION.CUSTOM_number_of_installments'] = $data->getCcParcelas2();
        }
        $parameters['PAYMENT.CODE'] = $this->getConfig()->getAcao() ? 'CC.DB' : 'CC.PA';
        $parameters['CONTACT.EMAIL'] = trim($order->getBillingAddress()->getEmail()) ? trim(utf8_decode($order->getBillingAddress()->getEmail())) : trim(utf8_decode($order->getCustomerEmail()));
        $parameters['NAME.GIVEN'] = utf8_decode($order->getBillingAddress()->getFirstname());
        $parameters['NAME.FAMILY'] = utf8_decode($order->getBillingAddress()->getLastname());
        return $parameters;
    }    

    private function preparePresentationParameters($amount) {
        $parameters = array();
        $parameters['PRESENTATION.CURRENCY'] = "BRL";
        $parameters['PRESENTATION.AMOUNT'] = $amount;
        return $parameters;
    }

    private function prepareCaptureRequestParameters($authorizationResponse,$data,$count) {
        $r = $authorizationResponse;
        $parameters = array();
        $parameters = $this->prepareCommonParameters($data->getCcType(),$count);
        $parameters = array_merge($parameters, $this->preparePresentationParameters($r['PRESENTATION.AMOUNT']));
        $parameters['IDENTIFICATION.REFERENCEID'] = $r['IDENTIFICATION.UNIQUEID'];
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $r['IDENTIFICATION.TRANSACTIONID'];
        $parameters['PAYMENT.CODE'] = "CC.CP";
        return $parameters;
    }

    private function getTransactionChannel1($config,$ccType) {

        $redecard = explode(',',$config->getCctypesRcard());
        $cielo = explode(',',$config->getCctypesCielo());
        $firstdata = explode(',',$config->getCctypesFirstdata());

        if(array_search($ccType.'_R',$redecard)!==false){
            $channel = 'transaction_channel_redecard';
        }elseif(array_search($ccType.'_C',$cielo)!==false){
            $channel = 'transaction_channel_cielo';
        }elseif(array_search($ccType.'_F',$firstdata)!==false){
            $channel = 'transaction_channel_firstdata';
        }

        return trim($config->getData($channel));
    }
    
    private function getTransactionChannel2($config,$ccType) {

        $redecard = explode(',',$config->getCctypesRcard());
        $cielo = explode(',',$config->getCctypesCielo());
        $firstdata = explode(',',$config->getCctypesFirstdata());

        if(array_search($ccType.'_R',$redecard)!==false){
            $channel = 'transaction_channel_redecard';
        }elseif(array_search($ccType.'_C',$cielo)!==false){
            $channel = 'transaction_channel_cielo';
        }elseif(array_search($ccType.'_F',$firstdata)!==false){
            $channel = 'transaction_channel_firstdata';
        }

        return trim($config->getData($channel));
    }    
    
    private function getTID($result){
        
        $arrayTID = array();
        if(isset($result['PROCESSING.CONNECTORDETAIL.ConnectorTxID1'])){
            $arrayTID['ConnectorTxID1'] = $result['PROCESSING.CONNECTORDETAIL.ConnectorTxID1'];
		}
        if(isset($result['PROCESSING.CONNECTORDETAIL.ConnectorTxID2'])){
            $arrayTID['ConnectorTxID2'] = $result['PROCESSING.CONNECTORDETAIL.ConnectorTxID2'];
        }        
        if(isset($result['PROCESSING.CONNECTORDETAIL.ConnectorTxID3'])){
            $arrayTID['ConnectorTxID3'] = $result['PROCESSING.CONNECTORDETAIL.ConnectorTxID3'];
        }
        if(isset($result['PROCESSING.CONNECTORDETAIL.LR'])){
            $arrayTID['LR'] = $result['PROCESSING.CONNECTORDETAIL.LR'];
        }
        if(isset($result['PROCESSING.CONNECTORDETAIL.NSU'])){
            $arrayTID['NSU'] = $result['PROCESSING.CONNECTORDETAIL.NSU'];
        }      
        
        return $arrayTID;        
        
    }
    
    private function getTID2($result){
        
        $arrayTID = array();
        if(isset($result['PROCESSING.CONNECTORDETAIL.ConnectorTxID1'])){
            $arrayTID['ConnectorTxID1'] = $result['PROCESSING.CONNECTORDETAIL.ConnectorTxID1'];
        }
        if(isset($result['PROCESSING.CONNECTORDETAIL.ConnectorTxID2'])){
            $arrayTID['ConnectorTxID2'] = $result['PROCESSING.CONNECTORDETAIL.ConnectorTxID2'];
        }        
        if(isset($result['PROCESSING.CONNECTORDETAIL.ConnectorTxID3'])){
            $arrayTID['ConnectorTxID3'] = $result['PROCESSING.CONNECTORDETAIL.ConnectorTxID3'];
        }
        if(isset($result['PROCESSING.CONNECTORDETAIL.LR'])){
            $arrayTID['LR'] = $result['PROCESSING.CONNECTORDETAIL.LR'];
        }
        if(isset($result['PROCESSING.CONNECTORDETAIL.NSU'])){
            $arrayTID['NSU'] = $result['PROCESSING.CONNECTORDETAIL.NSU'];
        }      
        
        return $arrayTID;          
        
    }  
    
    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     *
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        /*
         * calling parent validate function
         */
        parent::validate();
        
        if(Mage::getStoreConfig('onestepcheckout/general/is_enabled') && !Mage::registry('osc_place_order')){
            return $this;
        }        
        
        $this->_validateFirstCc();
        $this->_validateSecondCc();
        return $this;
    }    
    
    /*
     * Validate First Creditcard
     */
    public function _validateFirstCc() {
        
        $helper = Mage::helper('gwap');
        $info = $this->getInfoInstance();

        $errorMsg = false;
        $availableTypesC = explode(',', Mage::getStoreConfig('payment/gwap_cc/cctypes_cielo'));
        $availableTypesR = explode(',', Mage::getStoreConfig('payment/gwap_cc/cctypes_rcard'));
        $availableTypesF = explode(',', Mage::getStoreConfig('payment/gwap_cc/cctypes_firstdata'));
        
        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';
        if (in_array($info->getCcType().'_C', $availableTypesC)
                || in_array($info->getCcType().'_R', $availableTypesR)
                    || in_array($info->getCcType().'_F', $availableTypesF)) {
            if ($helper->validateCcNum($ccNumber)
                    // Other credit card type number validation
                    || ($helper->OtherCcType($info->getCcType()) && $helper->validateCcNumOther($ccNumber))) {

                $ccType = 'ELO';
                $ccTypeRegExpList = array(
                    'VISA' => '/^4[0-9]{12}([0-9]{3})?$/',
                    'MASTER' => '/^5[1-5][0-9]{14}$/',
                    'DINERS' => '/^3[0,6,8][0-9]{12}/',
                    'AMEX' => '/^3[47][0-9]{13}$/',
                    'DISCOVER' => '/^6011[0-9]{4}[0-9]{4}[0-9]{4}$/',
                    'JCB' => '/^(?:2131|1800|35\d{3})\d{11}$/'
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch => $ccTypeRegExp) {
                    if($helper->OtherCcType($info->getCcType())){
                        $ccType = $ccTypeMatch;
                        break;
                    }elseif (preg_match($ccTypeRegExp, $ccNumber)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }  
                }

                if (!$helper->OtherCcType($info->getCcType()) && $ccType != $info->getCcType()) {
                    $errorMsg = $this->_getHelper()->__('Número de cartão de crédito não corresponde ao tipo de cartão de crédito (Cartão 1).');
                }
            } else {
                $errorMsg = $this->_getHelper()->__('Número de cartão inválido (Cartão 1).');
            }
        } else {
            $errorMsg = $this->_getHelper()->__('Tipo de cartão de crédito não permitido para esse método de pagamento (Cartão 1).');
        }

        //validate credit card verification number
        if ($errorMsg === false) {
            $verifcationRegEx = $helper->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                $errorMsg = $this->_getHelper()->__('Por favor informe um número de verificação válido (Cartão 1)');
            }
        }

        if ($ccType != 'SS' && !$helper->validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = $this->_getHelper()->__('Data de expiração cartão inválida (Cartão 1).');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

    }
    
    /*
     * Validate Second Creditcard
     */
    public function _validateSecondCc() {

        $helper = Mage::helper('gwap');
        $info = $this->getInfoInstance();
        
        $errorMsg = false;
        $ccNumber = $info->getCcNumber();        
        $ccNumber2 = $info->getAdditionalInformation('gwapCcNumber2');

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);        
        $ccNumber2 = preg_replace('/[\-\s]+/', '', $ccNumber2);
        $info->setAdditionalInformation('gwapCcNumber2',$ccNumber2);

        if ($ccNumber == $ccNumber2){
            $errorMsg = Mage::helper('payment')->__('Os Cartões de Crédito precisam ser diferentes.');
        }

        if ($info->getAdditionalInformation('gwapCcAmount') <= 0 ||
            Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal() <= $info->getAdditionalInformation('gwapCcAmount')) {
            $errorMsg = Mage::helper('payment')->__('O valor do pagamento deve ser maior que Zero e Menor que o Total da compra (Cartão 1).');
        }        
        
        $availableTypesC = explode(',', Mage::getStoreConfig('payment/gwap_cc/cctypes_cielo'));
        $availableTypesR = explode(',', Mage::getStoreConfig('payment/gwap_cc/cctypes_rcard'));
        $availableTypesF = explode(',', Mage::getStoreConfig('payment/gwap_cc/cctypes_firstdata'));

        $ccType = '';
        if (in_array($info->getAdditionalInformation('gwapCcType2').'_C', $availableTypesC)
                || in_array($info->getAdditionalInformation('gwapCcType2').'_R', $availableTypesR)
                    || in_array($info->getAdditionalInformation('gwapCcType2').'_F', $availableTypesF)) {
            if ($helper->validateCcNum($ccNumber2)
                    // Other credit card type number validation
                    || ($helper->OtherCcType($info->getAdditionalInformation('gwapCcType2')) && $helper->validateCcNumOther($ccNumber2))) {

                $ccType = 'ELO';
                $ccTypeRegExpList = array(
                    'VISA' => '/^4[0-9]{12}([0-9]{3})?$/',
                    'MASTER' => '/^5[1-5][0-9]{14}$/',
                    'DINERS' => '/^3[0,6,8][0-9]{12}/',
                    'AMEX' => '/^3[47][0-9]{13}$/',
                    'DISCOVER' => '/^6011[0-9]{4}[0-9]{4}[0-9]{4}$/',
                    'JCB' => '/^(?:2131|1800|35\d{3})\d{11}$/'
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch => $ccTypeRegExp) {
                    if($helper->OtherCcType($info->getAdditionalInformation('gwapCcType2'))){
                        $ccType = $ccTypeMatch;
                        break;
                    }elseif (preg_match($ccTypeRegExp, $ccNumber2)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }  
                }
                if (!$helper->OtherCcType($info->getAdditionalInformation('gwapCcType2')) && $ccType != $info->getAdditionalInformation('gwapCcType2')) {
                    $errorMsg = $this->_getHelper()->__('Número de cartão de crédito não corresponde ao tipo de cartão de crédito (Cartão 2).');
                }
            } else {
                $errorMsg = $this->_getHelper()->__('Número de cartão inválido (Cartão 2).');
            }
        } else {
            $errorMsg = $this->_getHelper()->__('Tipo de cartão de crédito não permitido para esse método de pagamento (Cartão 2).');
        }

        //validate credit card verification number
        if ($errorMsg === false) {
            $verifcationRegEx = $helper->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getAdditionalInformation('gwapCcType2')]) ? $verifcationRegEx[$info->getAdditionalInformation('gwapCcType2')] : '';
            if (!$info->getAdditionalInformation('gwapCcCid2') || !$regExp || !preg_match($regExp, $info->getAdditionalInformation('gwapCcCid2'))) {
                $errorMsg = $this->_getHelper()->__('Por favor informe um número de verificação válido (Cartão 2).');
            }
        }

        if ($ccType != 'SS' && !$helper->validateExpDate($info->getAdditionalInformation('gwapCcExpYear2'), $info->getAdditionalInformation('gwapCcExpMonth2'))) {
            $errorMsg = $this->_getHelper()->__('Data de expiração cartão inválida (Cartão 2.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }
        
    }     
    
}
