<?php

require_once 'Zend/Log.php';

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
class Allpago_Gwap_Model_Methods_Cc extends Mage_Payment_Model_Method_Cc {

    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';

    protected $_code = 'gwap_cc';
    protected $_formBlockType = 'gwap/form_cc';
    protected $_infoBlockType = 'gwap/info_cc';
    protected $_allowCurrencyCode = array('BRL', 'USD');
    protected $_canSaveCc = false;
    protected $_canCapture = true;
    protected $_resultCode = '';
    protected $_TID = '';
    protected $_cc = '';    
    protected $_rg = ''; 
    private   $qtd_tentativas = 1;
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
        
        $info->setCcType($data->getData('gwap_cc_cc_type'))
                ->setCcOwner($data->getData('gwap_cc_cc_owner'))
                ->setCcLast4(substr($data->getData('gwap_cc_cc_number'), -4))
                ->setCcNumber(preg_replace("/[^a-zA-Z0-9\s]/", "", $data->getData('gwap_cc_cc_number')))
                ->setCcCid(preg_replace("/[^a-zA-Z0-9\s]/", "", $data->getData('gwap_cc_cc_cid')))
                ->setCcExpMonth($data->getData('gwap_cc_cc_exp_month'))
                ->setCcParcelas($data->getData('gwap_cc_parcelas'))
                ->setCcExpYear($data->getData('gwap_cc_cc_exp_year'))
                ->setCcSsIssue($data->getData('gwap_cc_cc_ss_issue'))
                ->setCcSsStartMonth($data->getData('gwap_cc_cc_start_month'))
                ->setCcSsStartYear($data->getData('gwap_cc_cc_ss_start_month'));
        
        if(Mage::getStoreConfig('payment/gwap_oneclick/active')){
             $info->setAdditionalInformation('GwapCheckOneclick', $data->getData('gwap_cc_check_oneclick'));
        }        
        
        if(Mage::getStoreConfig('allpago/clearsale/active')){
             $info->setAdditionalInformation('GwapSessionId', $data->getData('gwap_session_id'));
        }
        
        Mage::getModel('core/session')->setGwapCcId();
        Mage::getModel('core/session')->setGwapCcCcNumber();        
        Mage::getModel('core/session')->setGwapCcId($data->getGwapCcCcCid());
        Mage::getModel('core/session')->setGwapCcCcNumber($data->getGwapCcCcNumber());        
        return $this;
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
        
        
        if (!Mage::getStoreConfig('payment/gwap_cc/active')) {
            return false;
        }
        
        return true;
    }

    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }
        //$info->setCcCidEnc($info->encrypt($info->getCcCid()));
        //$info->setCcNumber(null)
        //->setCcCid(null);
        return $this;
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

        $parameters = $this->prepareAuthenticationRequestParameters($order, $cc);
        
        $url = $this->getRequestURL();

        // Se true, não efetua PA
        if ($this->getConfig()->getAcao()) { 
            if(Mage::getStoreConfig('allpago/clearsale/active')){
                $gwap->setClearsaleInfo($gwap->getInfo());
            }            
            //Autorização instantânea
            if($this->_cc){ //Salva dados para RG (sem tabela)
                if (Mage::getStoreConfig('payment/gwap_oneclick/active')) {
                    $this->_rg = $this->_cc;
                }
                $this->_cc = Mage::helper('core')->encrypt(serialize($parameters));                  
            }else{
                $gwap->clearInstance();
                $gwap = Mage::getModel('gwap/order')->load($orderId, 'order_id');                
                if (Mage::getStoreConfig('payment/gwap_oneclick/active')) {
                    $gwap->setRegistrationCc($gwap->getInfo());
                }
                $gwap->setInfo(Mage::helper('core')->encrypt(serialize($parameters)));
                $gwap->save();
            }
            return $this;
        }
        
        $postString = $this->buildPostString($parameters);
        $response = $this->makeCurlRequest($url, $postString);

        if ($this->_resultCode != '90') {
            Mage::throwException('Payment code: ' . $response['PAYMENT.CODE'] . ' (' . $response['PROCESSING.REASON'] . ' - ' . $response['PROCESSING.RETURN'] . ')');
        }

        // prepare parameters to capture after Pre Authorize success            
        $captureParams = $this->prepareCaptureRequestParameters($response,$cc);
        
        // Autorização instantânea
        if($this->_cc){ 
            $this->_rg = $this->_cc;
            $this->_cc = Mage::helper('core')->encrypt(serialize($captureParams));
        //Não salvar dados no banco            
        }else{
            if($this->isOneclickOrder($cc)){
                $gwap->setRegistrationCc($gwap->getInfo());
            }
        }
        if(Mage::getStoreConfig('allpago/clearsale/active')){
            $gwap->setClearsaleInfo($gwap->getInfo());
        }
        $gwap->setInfo(Mage::helper('core')->encrypt(serialize($captureParams)));
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

            $url = $this->getRequestURL();

            if($this->_cc){
                $cc = new Varien_Object(unserialize(Mage::helper('core')->decrypt($this->_cc)));
            }else{
                $cc = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwap->getInfo())));            
            }

            $parameters = $cc->toArray();
            $postString = $this->buildPostString($parameters);
            $cc = '';

            $response = $this->makeCurlRequest($url, $postString);
            if ($this->_resultCode != '90') {
                $errorMsg = 'Payment code: ' . $response['PAYMENT.CODE'] . ' (' . $response['PROCESSING.REASON'] . ' - ' . $response['PROCESSING.RETURN'] . ')';
                //Captura manual ativada
                if(Mage::getStoreConfig('payment/gwap_cc/captura')){
                    $log->add($gwap->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_ERROR, 'Ocorreu um erro', $errorMsg);
                }
                Mage::throwException($errorMsg);
            }
            $log->add($gwap->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_CAPTURED, 'Pagamento capturado');

            //Salva UNIQUEID da captura para possibilitar estorno
            $gwap->setInfo(serialize(array('UNIQUEID'=>$response['IDENTIFICATION.UNIQUEID'])));
            $gwap->setCaptureResult(serialize($this->_TID));

            $gwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CAPTURED);
            $gwap->setErrorCode(null);
            $gwap->setErrorMessage(null);
            $gwap->setTries(0);
            $gwap->setAbandoned(0);
            $gwap->save();

            //Captura instantanea
            if($this->_rg){
                $cc = new Varien_Object($this->_rg);
            //Lote            
            }else{
                $cc = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwap->getRegistrationCc())));
            }

            // Se oneclick ativado cria(RG)/atualiza(RR) no gateway
            if ($this->isOneclickOrder($cc)) {
                $customerId = $payment->getOrder()->getCustomerId();
                $registrationId = $this->getRegistrationInfo($customerId,substr($cc->getCcNumber(), -4));
                if (!$registrationId) {
                    /*/Update
                    $response = $this->fetchCustomerRegistration($gwap, $registrationId);
                    if ($this->_resultCode != '90') {
                        $gwap->setRegistrationCc(null);
                        $gwap->save();
                        $errorMsg = 'Payment code: ' . $response['PAYMENT.CODE'] . ' (' . $response['PROCESSING.REASON'] . ' - ' . $response['PROCESSING.RETURN'] . ')';
                        //Captura manual ativada
                        if(Mage::getStoreConfig('payment/gwap_cc/captura')){
                            $log->add($gwap->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_ERROR, 'Ocorreu um erro', $errorMsg);
                        }                    
                        Mage::throwException($errorMsg);
                    }*/
                    $this->newRegistrationInfo($customerId, $gwap);
                }
            }

            $gwap->setRegistrationCc(null);
            $gwap->save();
        }
        return $this;
    }

    public function authorizeNow($order, $cc) {

        Mage::getSingleton('core/session')->setAllpagoNewOrderSent(1);
        try {
            $this->_cc = '';
            $this->_cc = $cc;
            $log = Mage::getModel('allpago_mc/log');
            $this->authorize($order->getPayment(), $order->getGrandTotal());

            //Atualiza o objeto, para caso tenha sido atualizado no método Authorize
            $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($order->getId(), 'order_id');
            //Salva log
            if (!Mage::getStoreConfig('payment/gwap_cc/acao')) {
                $log->add($order->getId(), 'Payment', 'authorize()', Allpago_Mc_Model_Mc::STATUS_AUTHORIZED, 'Pagamento autorizado');
            }
            //Se Antifraude estiver ligado Status = authorized
            $gatewayPayment->setStatus(($this->getAntifraude($order) ? Allpago_Mc_Model_Mc::STATUS_AUTHORIZED : Allpago_Mc_Model_Mc::STATUS_CAPTUREPAYMENT));
            $gatewayPayment->setErrorCode(null);
            $gatewayPayment->setErrorMessage(null);
            $time = Mage::getStoreConfig('allpago/allpago_mc/tempo_espera');
            $gatewayPayment->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s", strtotime("-{$time} hours")));
            $gatewayPayment->setTries(0);
            $gatewayPayment->setAbandoned(0);
            $gatewayPayment->save();

            //DB
            if ($this->getConfig()->getAcao()) { // STATUS_CAPTUREPAYMENT

                try {
                    $this->capture($order->getPayment(), $order->getGrandTotal());

                    //Gera invoice e manda e-mail
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
                    $this->cancelOrder($order, $e->getMessage());
                    $this->failureRedirect($e->getMessage());
                    return false;
                }
            } else {
                $order->sendNewOrderEmail();
                Mage::getSingleton('core/session')->setAllpagoNewOrderSent();
                
                //if(Mage::getStoreConfig('allpago/clearsale/active')
                //        && Mage::getStoreConfig('allpago/clearsale/produto') == 'clearid' 
                //            && Mage::getStoreConfig('allpago/clearsale/clearid_questionario')){
                //    return true;
                //}                

                $block = Mage::app()->getLayout()->getMessagesBlock();
                $block->addSuccess('Transação autorizada com sucesso');
                return true;
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->setAllpagoNewOrderSent();
            $this->cancelOrder($order, $e->getMessage());
            $this->failureRedirect($e->getMessage());
            return false;
        }
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
            //$gatewayPayment->setInfo(null);
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

    public function failureRedirect($errorMsg) {
        $this->getSession()->setGwapFailure($errorMsg);
        Mage::app()
                ->getResponse()
                ->setRedirect(Mage::getUrl('allpago_gwap/checkout/failure'));
        Mage::app()
                ->getResponse()
                ->sendResponse();
        exit;
    }

    public function removeCredit($order){
        $gwap = Mage::getModel('gwap/order')->load($order->getId(),'order_id');
        if($gwap->getStatus() == 'captured' 
                || ($gwap->getStatus() == 'authorized' && !$this->getConfig()->getAcao()) ){
         
            $identification = unserialize($gwap->getInfo());
         
            $now       = date('d', Mage::getModel('core/date')->timestamp(time()));
            $createdAt = date('d', Mage::getModel('core/date')->timestamp($order->getCreatedAt()));

            if ($now > $createdAt){
                $response = $this->reversalOrRefund($order->getIncrementId(),$identification['UNIQUEID'],'RF',$gwap->getCcType());  
            }else{
                $response = $this->reversalOrRefund($order->getIncrementId(),$identification['UNIQUEID'],'RV',$gwap->getCcType());  
            }

            if ($this->_resultCode != '90') {
                Mage::throwException('Payment code: ' . $response['PAYMENT.CODE'] . ' (' . $response['PROCESSING.REASON'] . ' - ' . $response['PROCESSING.RETURN'] . ')');
            }
        }else {
            Mage::throwException('Não é possível estornar um pedido ainda não autorizado ou capturado.');
        }
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

    /*
     * Validate
     */

    public function validate() {
        /*
         * calling parent validate function
         */
        $helper = Mage::helper('gwap');
                
        $info = $this->getInfoInstance();
        
        if ($info instanceof Mage_Sales_Model_Order_Payment) {
            $billingCountry = $info->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $info->getQuote()->getBillingAddress()->getCountryId();
        }
        if (!$this->canUseForCountry($billingCountry)) {
            Mage::throwException($this->_getHelper()->__('Selected payment type is not allowed for billing country.'));
        }

        $errorMsg = false;
        $availableTypesC = explode(',', $this->getConfigData('cctypes_cielo'));
        $availableTypesR = explode(',', $this->getConfigData('cctypes_rcard'));
        $availableTypesF = explode(',', $this->getConfigData('cctypes_firstdata'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';
        if (in_array($info->getCcType().'_C', $availableTypesC)
                || in_array($info->getCcType().'_R', $availableTypesR)
                    || in_array($info->getCcType().'_F', $availableTypesF)) {
            if ($this->validateCcNum($ccNumber)
                    // Other credit card type number validation
                    || ($helper->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {

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
                    $errorMsg = $this->_getHelper()->__('O número do cartão de crédito não bate com o tipo de cartão informado.');
                }
            } else {
                $errorMsg = $this->_getHelper()->__('Número de cartão de crédito inválido.');
            }
        } else {
            $errorMsg = $this->_getHelper()->__('Tipo de Cartão de Crédito Credit não permitido para esse método de pagamento.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $helper->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                $errorMsg = $this->_getHelper()->__('Por favor informe um número verificação válido para este cartão de crédito.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = $this->_getHelper()->__('Data de expiração do cartão de crédito inválida.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
            //throw Mage::exception('Mage_Payment', $errorMsg, $errorCode);
        }

        //This must be after all validation conditions
        if ($this->getIsCentinelValidationEnabled()) {
            $this->getCentinelValidator()->validate($this->getCentinelValidationData());
        }
    }

    private function makeCurlRequest($url, $postString) {
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
        
        $arrayTID = array();
        if(isset($returnvalue['PROCESSING.CONNECTORDETAIL.ConnectorTxID1'])){
            $arrayTID['ConnectorTxID1'] = $returnvalue['PROCESSING.CONNECTORDETAIL.ConnectorTxID1'];
        }
        if(isset($returnvalue['PROCESSING.CONNECTORDETAIL.ConnectorTxID2'])){
            $arrayTID['ConnectorTxID2'] = $returnvalue['PROCESSING.CONNECTORDETAIL.ConnectorTxID2'];
        }        
        if(isset($returnvalue['PROCESSING.CONNECTORDETAIL.ConnectorTxID3'])){
            $arrayTID['ConnectorTxID3'] = $returnvalue['PROCESSING.CONNECTORDETAIL.ConnectorTxID3'];
        }
        if(isset($returnvalue['PROCESSING.CONNECTORDETAIL.LR'])){
            $arrayTID['LR'] = $returnvalue['PROCESSING.CONNECTORDETAIL.LR'];
        }
        if(isset($returnvalue['PROCESSING.CONNECTORDETAIL.NSU'])){
            $arrayTID['NSU'] = $returnvalue['PROCESSING.CONNECTORDETAIL.NSU'];
        }        
        $this->_TID = $arrayTID;
        
        $this->_resultCode = '';
        $resultCode = explode('.', $returnvalue['PROCESSING.CODE']);
        $this->_resultCode = $resultCode[2];

        return $returnvalue;
    }

    private function buildPostString($parameters) {
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

    private function getRequestURL() {
        $auth = $this->getAuthConfig();
        if ($auth->getAmbiente() == 'LIVE') {
            return 'https://ctpe.net/frontend/payment.prc';
        } else { //'CONNECTOR_TEST'
            return 'https://test.ctpe.net/frontend/payment.prc';
        }
    }

    private function getRegistrationInfo($customerId,$ccLast4) {
        $oneclick = Mage::getModel('gwap/oneclick')->getCollection()
                    ->addFieldToFilter('customer_id',$customerId)
                    ->addFieldToFilter('cc_last4',$ccLast4);
        
        return $oneclick->getFirstItem()->getRegistrationId();
    }

    private function newRegistrationInfo($customerId, $gwapOrder) {

        $response = $this->fetchCustomerRegistration($gwapOrder);
        
        //Captura instantanea
        if($this->_rg){
            $cc = new Varien_Object($this->_rg);
        //Lote
        }else{
            $cc = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwapOrder->getRegistrationCc())));
        }

        if ($this->_resultCode != '90') {
            $gwapOrder->setRegistrationCc(null);
            $gwapOrder->save();
            Mage::throwException('Payment code: ' . $response['PAYMENT.CODE'] . ' (' . $response['PROCESSING.REASON'] . ' - ' . $response['PROCESSING.RETURN'] . ')');
        }

        //Cria registro na tabela oneclick
        $newRegistry = Mage::getModel('gwap/oneclick');
        $newRegistry->setCustomerId($customerId);
        $newRegistry->setRegistrationId($response['IDENTIFICATION.UNIQUEID']);
        $newRegistry->setCcLast4(substr($cc->getCcNumber(), -4));
        $newRegistry->setType($cc->getCcType());
        $newRegistry->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        $newRegistry->save();

        $log = Mage::getModel('allpago_mc/log');
        $log->add($gwapOrder->getOrderId(), 'Payment', 'capture()', Allpago_Mc_Model_Mc::STATUS_REGISTRATION, 'Cliente registrado (oneclick)');
        
        return $newRegistry->getRegistrationId();
    }

    // Criar/atualizar o registro
    private function fetchCustomerRegistration($gwapOrder, $update = null) {
        
        $orderId = $gwapOrder->getOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        
        if($this->_rg){
            $cc = new Varien_Object($this->_rg);
        }else{
            $cc = new Varien_Object(unserialize(Mage::helper('core')->decrypt($gwapOrder->getRegistrationCc())));
        }      
        
        $url = $this->getRequestURL();
        if ($update) {
            $parameters = $this->prepareUpdateRegistrationRequestParameters($order, $cc, $update);
        } else {
            $parameters = $this->prepareRegistrationRequestParameters($order, $cc);
        }

        $postString = $this->buildPostString($parameters);
        $response = $this->makeCurlRequest($url, $postString);
        return $response;
    }

    private function prepareRegistrationRequestParameters($order, $cc) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $order->getCustomerId().(substr($cc->getCcNumber(),-4));
        $parameters = array_merge($parameters, $this->prepareCommonParameters($cc));
        //Dados da compra
        $parameters = array_merge($parameters, $this->prepareAccountParameters($cc));
        $parameters = array_merge($parameters, $this->prepareAddressParameters($order));
        $parameters['PAYMENT.CODE'] = 'CC.RG';
        $parameters['CONTACT.EMAIL'] = trim($order->getBillingAddress()->getEmail()) ? trim(utf8_decode($order->getBillingAddress()->getEmail())) : trim(utf8_decode($order->getCustomerEmail()));
        $parameters['NAME.GIVEN'] = utf8_decode($order->getBillingAddress()->getFirstname());
        $parameters['NAME.FAMILY'] = utf8_decode($order->getBillingAddress()->getLastname());

        return $parameters;
    }

    private function prepareUpdateRegistrationRequestParameters($order, $data, $registrationId) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();

        $parameters['IDENTIFICATION.TRANSACTIONID'] = 'Customer id ' . $order->getCustomerId();
        $parameters['IDENTIFICATION.REFERENCEID'] = $registrationId;
        $parameters = array_merge($parameters, $this->prepareCommonParameters($data));
        //Dados da compra
        $parameters = array_merge($parameters, $this->prepareAccountParameters($data));
        $parameters = array_merge($parameters, $this->prepareAddressParameters($order));
        $parameters['PAYMENT.CODE'] = 'CC.RR';
        $parameters['CONTACT.EMAIL'] = trim($order->getBillingAddress()->getEmail()) ? trim(utf8_decode($order->getBillingAddress()->getEmail())) : trim(utf8_decode($order->getCustomerEmail()));
        $parameters['NAME.GIVEN'] = utf8_decode($order->getBillingAddress()->getFirstname());
        $parameters['NAME.FAMILY'] = utf8_decode($order->getBillingAddress()->getLastname());

        return $parameters;
    }

    private function prepareCommonParameters($data) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();
        $parameters['RESPONSE.VERSION'] = '1.0';
        $parameters['TRANSACTION.MODE'] = $auth->getAmbiente();
        $parameters['TRANSACTION.RESPONSE'] = 'SYNC';
        $parameters['SECURITY.SENDER'] = $this->getSecuritySender($auth);
        $parameters['TRANSACTION.CHANNEL'] = $this->getTransactionChannel($config,$data);
        $parameters['USER.LOGIN'] = $this->getUserLogin($auth);
        $parameters['USER.PWD'] = $this->getUserPassword($auth);
        return $parameters;
    }

    private function prepareAccountParameters($cc) {
        $parameters['ACCOUNT.HOLDER'] = $cc->getCcOwner();
        $parameters['ACCOUNT.NUMBER'] = $cc->getCcNumber();
        $parameters['ACCOUNT.BRAND'] = $cc->getCcType();
        $parameters['ACCOUNT.EXPIRY_MONTH'] = $cc->getCcExpMonth();
        $parameters['ACCOUNT.EXPIRY_YEAR'] = $cc->getCcExpYear();
        $parameters['ACCOUNT.VERIFICATION'] = $cc->getCcCid();
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

    private function prepareAuthenticationRequestParameters($order, $data) {
        $auth = $this->getAuthConfig();
        $config = $this->getConfig();
        $parameters = array();
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $order->getIncrementId();
        $parameters = array_merge($parameters, $this->prepareCommonParameters($data));
        $parameters = array_merge($parameters, $this->prepareAccountParameters($data));
        $parameters = array_merge($parameters, $this->prepareAddressParameters($order));
        $parameters = array_merge($parameters, $this->preparePresentationParameters(number_format($order->getGrandTotal(), 2, '.', '')));
        if ($data->getCcParcelas() > 1) {
            $parameters['CRITERION.CUSTOM_number_of_installments'] = $data->getCcParcelas();
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

    private function prepareCaptureRequestParameters($authorizationResponse,$data) {
        $r = $authorizationResponse;
        $parameters = array();
        $parameters = $this->prepareCommonParameters($data);
        $parameters = array_merge($parameters, $this->preparePresentationParameters($r['PRESENTATION.AMOUNT']));
        $parameters['IDENTIFICATION.REFERENCEID'] = $r['IDENTIFICATION.UNIQUEID'];
        $parameters['IDENTIFICATION.TRANSACTIONID'] = $r['IDENTIFICATION.TRANSACTIONID'];
        $parameters['PAYMENT.CODE'] = "CC.CP";
        if ($data->getCcParcelas() > 1) {
            $parameters['CRITERION.CUSTOM_number_of_installments'] = $data->getCcParcelas();
        }        
        return $parameters;
    }

    private function getSecuritySender($config) {
        return trim($config->getSecuritySender());
    }

    private function getTransactionChannel($config,$cc) {

        if(is_object($cc)){
            $ccType = $cc->getCcType();
        }else{
            $ccType = $cc;
        }
        
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

    private function getUserLogin($config) {
        return trim($config->getUserLogin());
    }

    private function getUserPassword($config) {
        return strval(Mage::helper("core")->decrypt($config->getUserPwd()));
    }
    
    private function isOneclickOrder($cc) {
        return Mage::getStoreConfig('payment/gwap_oneclick/active') 
                    && $cc->getGwapCcCheckOneclick() ? true : false;
                               
    }
    
    /**
     * Fazer pre-autorização quando:<br/>
     * -Fcontrol ativado e valor do pedido menor que a configuracao vlr_minimo no Fcontrol.<br/>
     * -Ou Clearsale ativado e valor do pedido menor que a configuracao vlr_minimo no Clearsale.<br/>
     */
    public function getAntifraude($order) {
        return Mage::getStoreConfig('allpago/fcontrol/active') && $order->getGrandTotal() >= Mage::getStoreConfig('allpago/fcontrol/vlr_minimo')
                    || Mage::getStoreConfig('allpago/clearsale/active') && $order->getGrandTotal() >= Mage::getStoreConfig('allpago/clearsale/vlr_minimo');
    }     
                        
    
}
