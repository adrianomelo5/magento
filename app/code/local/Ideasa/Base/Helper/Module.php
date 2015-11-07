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

final class Ideasa_Base_Helper_Module extends Mage_Core_Helper_Abstract {

    /**
     * Verifica se o módulo PagSeguro Direto existe.
     * 
     * @return type
     */
    public static function isPagSeguroDiretoExists() {
        error_reporting(0);
        $exists = class_exists('Ideasa_PagSeguroDireto_Helper_Module');
        error_reporting(1);

        return $exists;
    }
    
    /**
     * Verifica se o módulo PagSeguro Direto existe e está ativo.
     * 
     * @return type
     */
    public function isPagSeguroDiretoExistsAndActive() {
        return $this->isPagSeguroDiretoExists() && Mage::helper('pagsegurodireto')->isModuleActive();
    }
    
    /**
     * Verifica se o módulo Televendas existe.
     * 
     * @return type
     */
    public static function isTeleVendasExists() {
        error_reporting(0);
        $exists = class_exists('Ipagare_IpgTeleVendas_Helper_Module');
        error_reporting(1);

        return $exists;
    }
}