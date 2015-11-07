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

class Ideasa_Base_Helper_OneStepCheckout extends Mage_Core_Helper_Abstract {

    private $logger;

    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->logger = Ideasa_Base_Logger::getLogger(__CLASS__);
    }

    public function getVersion() {
        $version = $this->getCompleteVersion();
        if (!Mage::helper('base/stringUtils')->isEmpty($version)) {
            return substr($version, 0, 1);
        }
        return '';
    }

    public function getCompleteVersion() {
        $resource = Mage::getSingleton('core/resource');
        $conn = $resource->getConnection('core_read');
        $sql = "SELECT version FROM {$resource->getTableName('core_resource')} WHERE code='onestepcheckout_setup'";
        $version = $conn->fetchAll($sql);
        if (!Mage::helper('base/stringUtils')->isEmpty($version[0]['version'])) {
            return $version[0]['version'];
        }
        return null;
    }

    public function isActive() {
        return Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
    }

}

?>
