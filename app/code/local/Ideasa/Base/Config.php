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

final class Ideasa_Base_Config {

    /**
     * Códigos dos meios de pagamento implementados pelo Idea SA.
     * 
     */
    protected static $_paymentMethods = array('pagsegurodireto');
    
    /**
     * Identifica que a origem do pedido, tabela "sales_flat_order", campo "ipagare_order_orig" é "televendas".
     */
    const ORDER_ORIG_TELEVENDAS = 'televendas';
    
    /**
     * Retorna a lista com os métodos de pagamentos
     * 
     * @return type array
     */
    public static function listPaymentMethods() {
        return self::$_paymentMethods;
    }

}