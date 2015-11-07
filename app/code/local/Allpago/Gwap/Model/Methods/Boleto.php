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
class Allpago_Gwap_Model_Methods_Boleto extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'gwap_boleto';
    protected $_formBlockType = 'gwap/form_boleto';
    protected $_infoBlockType = 'gwap/info_boleto';
    protected $_allowCurrencyCode = array('BRL', 'USD');
    protected $_canSaveCc = false;
    protected $_canCapture = true;

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
        $info->setAdditionalInformation('GwapBoletoType', $data->getGwapBoletoType());
                
        return $this;
    }

    /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal() {
        return false;
    }

    public function getConfig() {
        return new Varien_Object(Mage::getStoreConfig('payment/gwap_boleto'));
    }

    /**
     * Authorize
     *
     * @param   Varien_Object $orderPayment
     * @param float $amount
     * @return  Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount) {
        
        $data = Mage::helper('gwap')->getGwapPaymentData($payment);
        $log = Mage::getModel('allpago_mc/log');
        $auth = mage::helper('gwap')->getAuthConfig();
        $config = $this->getConfig();
        $order = $payment->getOrder();   
        
        $gwap = Mage::getModel('gwap/order');
        $gwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CREATED);
        $gwap->setCreatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        $gwap->setInfo(Mage::helper('core')->encrypt(serialize($data->toArray())));
        $gwap->setType(Mage::getStoreConfig('payment/gwap_boleto/mc_type'));
        $gwap->setOrderId($order->getId()); 
        $gwap->save();        

        $url = '';
        if ($auth->getAmbiente() == 'LIVE') {
            $url = "https://ctpe.net/frontend/payment.prc";
        } elseif ($auth->getAmbiente() == 'CONNECTOR_TEST') {
            $url = "https://test.ctpe.net/frontend/payment.prc";
        }
        
        $parameters = mage::helper('gwap')->setOrder($order)->prepareBoletoData( $data->getGwapBoletoType() );
        
        if($config->getDebug()){
            Mage::log(print_r($parameters,true).print_r($data,true),null,'boleto_allpago.log');        
        }
        
        //prepare params
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

            $var = $key;
            
            $value = $$key;
            $result .= "$var=$value";
        }

        $strPOST = stripslashes($result);

        // open the request url for the Web Payment Frontend
        $cpt = curl_init();
        curl_setopt($cpt, CURLOPT_URL, $url);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($cpt, CURLOPT_USERAGENT, "php ctpepost");
        curl_setopt($cpt, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($cpt, CURLOPT_POST, 1);
        curl_setopt($cpt, CURLOPT_POSTFIELDS, $strPOST);
        //curl_setopt($cpt, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"));

        $curlresultURL = curl_exec($cpt);
        $curlerror = curl_error($cpt);
        $curlinfo = curl_getinfo($cpt);

        curl_close($cpt);
        
        $r_arr = explode("&", $curlresultURL);

        foreach ($r_arr AS $buf) {
            $temp = urldecode($buf);
            $temp = explode("=", $temp, 2);
            $postatt = $temp[0];
            $postvar = $temp[1];
            $returnvalue[$postatt] = $postvar;
        }
        
        //Zend_debug::dump($parameters);
        if( !isset($returnvalue['PROCESSING.CODE']) ){
            try{
            	Mage::throwException(Mage::helper('gwap')->__('Falha ao gerar boleto'));
            } catch (Exception $e) {
				$this->redirect($order,$e->getMessage());
            }        	
        }
        $resultCode = explode('.', $returnvalue['PROCESSING.CODE']);

        // validate Pre authorization - 90 success code
        if ($resultCode[2] != '90') {
            try{
            	Mage::throwException(Mage::helper('gwap')->__($returnvalue['PROCESSING.REASON'] . ' - ' . $returnvalue['PROCESSING.RETURN']));
            } catch (Exception $e) {
                $log->add($gwap->getOrderId(), '+ Conversao', 'authorize()', Allpago_Gwap_Model_Order::STATUS_ERROR, 'Ocorreu um erro', $e->getMessage());                
                $this->redirect($order,$e->getMessage());           
            }
        }

        if( $returnvalue['PROCESSING.CONNECTORDETAIL.EXTERNAL_SYSTEM_LINK'] ){
            $redirect_url = $returnvalue['PROCESSING.CONNECTORDETAIL.EXTERNAL_SYSTEM_LINK'];
        }else{
            $redirect_url = $returnvalue['PROCESSING.REDIRECT.URL'] . '?DC=' . $returnvalue['PROCESSING.REDIRECT.PARAMETER.DC'];
        }
        $gwap->setInfo($redirect_url);
        
        //Corrigir bug no link do Bradesco
        if(Mage::getStoreConfig('payment/gwap_boleto/types') == 'BRADESCO'){        
            $gwap->setInfo( str_replace('sepsBoletoRet/004591070', 'paymethods/boletoret/model1', $redirect_url ) );
        }
        
        $gwap->setStatus(Allpago_Gwap_Model_Order::STATUS_CAPTUREPAYMENT);
        $gwap->setErrorCode(null);
        $gwap->setErrorMessage(null);
        $gwap->setTries(0);
        $gwap->setAbandoned(0);
        $gwap->setUpdatedAt(Mage::getModel('core/date')->date("Y-m-d H:i:s"));
        $gwap->save();              
        $log->add($gwap->getOrderId(), 'Payment', 'authorize()', Allpago_Gwap_Model_Order::STATUS_AUTHORIZED, 'Boleto gerado (pendente de captura)');                

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
       
        $gwap = Mage::getModel('gwap/order')->load($payment->getOrder()->getId(), 'order_id');

        $order = $payment->getOrder();
        $incrementId = $order->getIncrementId();
        
        $auth = Mage::helper('gwap')->getAuthConfig();
        $config = $this->getConfig();        
        
        $url = '';
        if ($auth->getAmbiente() == 'LIVE') {
            $url = "https://ctpe.io/payment/query";
        } elseif ($auth->getAmbiente() == 'CONNECTOR_TEST') {
            $url = "https://test.ctpe.io/payment/query";
        }
        
        $transaction_type = 'transaction_channel_'.strtolower($order->getPayment()->getGwapBoletoType());
        
        $transaction_type = trim( $config->getData($transaction_type) );        
        
        $captureParams  = '<Request version="1.0">';
        $captureParams .= '<Header>';
        $captureParams .= '<Security sender="' . $auth->getSecuritySender() . '"/>';
        $captureParams .= '</Header>';
        $captureParams .= '<Query mode="'.($auth->getAmbiente()).'" level="CHANNEL" entity="'.$transaction_type.'" type="STANDARD">';
           
        $captureParams .= '<User login="' . ($auth->getUserLogin()) . '" pwd="' . (strval(Mage::helper("core")->decrypt($auth->getUserPwd()))) . '"/>';
        $captureParams .= '<Identification>';
        $captureParams .= '<TransactionID>'.$incrementId.'</TransactionID>';
        $captureParams .= '</Identification>';
        $captureParams .= '<Types>';
        $captureParams .= '<Type code="RC"/>';
        $captureParams .= '</Types>';
        $captureParams .= '</Query>';
        $captureParams .= '</Request>';
       
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_MUTE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "load=$captureParams");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $curlresultURL = curl_exec($ch);
        curl_close($ch);
        
        $result = new Varien_Simplexml_Config($curlresultURL);
        if( $result->getNode('Result') && $result->getNode('Result')->getAttribute('count')  > 0 ){
              $resultCode =  $result->getNode('Result/Transaction');
              foreach ($resultCode as $rc){
                  $code =  $rc->children()->descend('Processing')->getAttribute('code');
              }
              
        }else{
            Mage::throwException(Mage::helper('gwap')->__('Pagamento não efetuado'));
            return $this;        
        }
        
        $resultCode = explode('.', $code);

        //Início - Validação do total pago
        $payedAmount = 0;
        $paymentAmout = $result->getNode('Result/Transaction');
        foreach ($paymentAmout as $p) {
            $payedAmount = $p->children()->descend('Payment')->children()->descend('Clearing')->Amount->__toString();
        }

        if ((float)$payedAmount < (float)$amount) {
            //Muda o status do pedido
            $order = Mage::getModel('sales/order')->load($payment->getOrder()->getId())->setState(Mage_Sales_Model_Order::STATE_HOLDED, true)->save();
            Mage::throwException(Mage::helper('gwap')->__('Valor original (R$ '.number_format($amount, 2, '.', '').') difere do valor pago (R$ '.number_format($payedAmount, 2, '.', '').').'));
            return $this;
        }
        //Fim - Validação do total pago   
       
        // validate Pre authorization - 90 success code
        if ($resultCode[2] != '90') {
            Mage::throwException(Mage::helper('gwap')->__('Pagamento não efetuado'));
            return $this;
        }

        return $this;
    }
    
    public function redirect($order,$errorMsg) {   
       $log = Mage::getModel('allpago_mc/log');
       $gatewayPayment = Mage::getModel('allpago_mc/payment')->load($order->getId(),'order_id');
       $log->add($order->getId(), '+ Conversao', 'authorize()', 'error', 'Erro ao gerar boleto', $errorMsg);
       $gatewayPayment->setUpdatedAt(Zend_Date::now()->get('YYYY-MM-dd HH:mm:ss'));
       $gatewayPayment->save();
       $block = Mage::app()->getLayout()->getMessagesBlock();
       $block->addError('Erro ao gerar boleto ('. $errorMsg.')');                   
       Mage::app()
           ->getResponse()
           ->setRedirect(Mage::getUrl('allpago_gwap/checkout/failure'));
       Mage::app()
           ->getResponse()
           ->sendResponse();
       exit;
    }    

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping() {
        return false;
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
     * Using for multiple shipping address
     *
     */
    public function createFormBlock($name) {
        $block = $this->getLayout()->createBlock($this->_formBlockType, $name)
                ->setMethod('gwap_boleto')
                ->setPayment($this->getPayment())
                ->setTemplate('gwap/boleto/form.phtml');

        return $block;
    }

    /**
     * Get Frontend name by Code
     *
     * @return frontendNameString or $code if not found
     */
    public function getFrontendName($code) {
        foreach ($this->_isParcelamento["frontend"] as $arCode => $nFrontend)
            if ($code == $arCode)
                return $nFrontend;

        foreach ($this->_isBoleto["frontend"] as $arCode => $nFrontend)
            if ($code == $arCode)
                return $nFrontend;

        foreach ($this->_isDebito["frontend"] as $arCode => $nFrontend)
            if ($code == $arCode)
                return $nFrontend;

        return $code;
    }

    /**
     * Get gwap session namespace
     *
     * @return Allpago_Gwap_Model_Session
     */
    public function getSession() {
        return Mage::getSingleton('gwap/session');
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

    /**
     * Some process when invoice is created
     * 
     * @param Mage_Sales_Model_Invoice_Payment $payment
     * @return Allpago_Gwap_Model_Methods_boleto
     */
    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment) {
        return $this;
    }

    /**
     * Some process when order is validated
     * 
     * @param Mage_Sales_Model_Invoice_Payment $payment
     * @return Allpago_Gwap_Model_Methods_boleto
     */
    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment) {
        return $this;
    }

    /*
     * Validate
     */

    public function validate() {
        parent::validate();
    }

}
