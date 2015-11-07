<?php

/**
 * Allpago - Gwap Payment Module
 *
 * @title      Magento -> Custom Payment
 * @category   Payment Gateway
 * @package    Allpago_Gwap
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Model_Order extends Mage_Core_Model_Abstract {
    
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_CAPTUREPAYMENT = 'capture payment';
    const STATUS_CREATED = 'created';
    const STATUS_DENIED = 'denied';
    const STATUS_ERROR = 'error';
    const STATUS_FINISHED = 'finished';
    const STATUS_MAXTRIES = 'max tries';
    const STATUS_PROCESSING = 'processing';
    
    private $tipo_pagamentos = array('cc_visa'=>'Cartão Visa',
                                     'cc_master'=>'Cartão Master',
                                     'cc_diners'=>'Cartão Diners',
                                     'cc_amex'=>'Cartão Amex',
                                     'cc_elo'=>'Cartão Elo',
                                     'cc_discover'=>'Cartão Discover',
                                     'cc_hipercard'=>'Cartão Hipercard',
                                     'boleto_itau'=>'Boleto Itaú',
                                     'boleto_bradesco'=>'Boleto Bradesco'
                                );
    
    public function _construct() {
        $this->_init('gwap/order');
    }
    
    public function getArrayTipoPagamentos() {
        return $this->tipo_pagamentos;
    }

}