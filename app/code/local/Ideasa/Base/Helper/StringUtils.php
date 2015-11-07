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

class Ideasa_Base_Helper_StringUtils extends Mage_Core_Helper_Abstract {

    /**
     * Verifica se determinada valor é <strong>null</strong> ou <strong>vazio</strong>.
     * 
     * @param type $str
     * @return type
     */
    public static function isEmpty($str) {
        if ($str == null) {
            return true;
        }
        if (trim($str) == '') {
            return true;
        }
        return false;
    }

    public static function removeParentheses($str) {
        if (self::isEmpty($str)) {
            return $str;
        }
        $str = str_ireplace('(', '', $str);
        $str = str_ireplace(')', '', $str);
        return $str;
    }
}