<?php

/**
* 
* SuperEmpreendedor para Magento
* 
* @category     SuperEmpreendedor
* @packages     Base
* @copyright    Copyright (c) 2014 SuperEmpreendedor (http://www.superempreendedor.com/pagseguro)
* @version      1.16.2
* @license      http://www.superempreendedor.com/magento/licenca (Este arquivo Ã© propriedade do SuperEmpreendedor e nÃ£o pode ser copiado ou distribuÃ­do sem autorizaÃ§Ã£o.)
*
*/ 

class Ideasa_Base_Helper_Url extends Mage_Core_Helper_Abstract {
    /**
     * 
     */

    const ONE_STEP_CHECKOUT = '/onestepcheckout/';
    const OPC_CHECKOUT = '/checkout/onepage/';

    /**
     * Checkout Venda Mais
     */
    const CHECKOUT_VM = '/idecheckoutvm/';
    
    const CHECKOUT_ONEPAGECHECKOUT = '/onepagecheckout/';
    
    const CHECKOUT_ONESTEPCHECKOUT = '/onestepcheckout/';
    
    const CHECKOUT_SIMPLIFICADO = '/checkoutsimplificado/';

    /**
     * 
     */
    const TELEVENDAS = '/ipgtelevendas/pagamento/index/';

    /**
     *
     * @var type 
     */
    private static $logger;

    /**
     * Initialize resource model
     */
    protected function _construct() {
        
    }

    public function isInTelevendasPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::TELEVENDAS)) {
            return true;
        }
        return false;
    }

    public function isInOscPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::ONE_STEP_CHECKOUT)) {
            return true;
        }
        return false;
    }

    public function isInOpcPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::OPC_CHECKOUT)) {
            return true;
        }
        return false;
    }

    public function isInCvmPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::CHECKOUT_VM)) {
            return true;
        }
        return false;
    }
    
    public function isInOnePageCheckoutPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::CHECKOUT_ONEPAGECHECKOUT)) {
            return true;
        }
        return false;
    }
    
    public function isInOneStepCheckoutPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::CHECKOUT_ONESTEPCHECKOUT)) {
            return true;
        }
        return false;
    }
    
     public function isInCheckoutSimplificadoPayment() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, self::CHECKOUT_SIMPLIFICADO)) {
            return true;
        }
        return false;
    }


    public function printHeaders() {
        self::$logger = Ideasa_Base_Logger::getLogger(__CLASS__);
        $sessionCore = $_SESSION['core'];
        $visitorData = $sessionCore['visitor_data'];
        self::$logger->info("Informacoes do cliente: \n http_user_agent= {$visitorData['http_user_agent']} \n http_accept_language = {$visitorData['http_accept_language']} \n request_uri = {$visitorData['request_uri']} \n http_referer = {$visitorData['http_referer']} \n");
    }

}
