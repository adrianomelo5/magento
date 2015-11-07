<?php

/**
 * Allpago Module for Clearsale
 *
 * @title      Magento -> Custom Module for Clearsale
 * @category   Fraud Control Gateway
 * @package    Allpago_Clearsale
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
class Allpago_Clearsale_Model_Orders extends Mage_Core_Model_Abstract {
    const STATUS_APPROVED = 'approved';
    const STATUS_CAPTUREPAYMENT = 'capture payment';
    const STATUS_CREATED = 'created';
    const STATUS_DENIED = 'denied';
    const STATUS_ERROR = 'error';
    const STATUS_CLEARSALE = 'clearsale';
    const STATUS_MAXTRIES = 'max tries';
    const STATUS_SEND = 'analise';

    private $status_clearsale = array('APA (Aprovação Automática)'=>'APA',
                                      'APM (Aprovação Manual)'=>'APM',
                                      'RPM (Reprovado Sem Suspeita)'=>'RPM',
                                      'AMA (Análise manual)'=>'AMA',
                                      'ERR (Erro)'=>'ERR',
                                      'NVO (Novo)'=>'NVO',
                                      'SUS (Suspensão Manual)'=>'SUS',
                                      'CAN (Cancelado pelo Cliente)'=>'CAN',
                                      'FRD (Fraude Confirmada)'=>'FRD',
                                      'RPA (Reprovação Automática)'=>'RPA',        
                                      'RPP (Reprovação Por Política)'=>'RPP',                
                                      'APRLOJA'=>'APR LOJA',                
                                    );

    public function _construct() {
        $this->_init('clearsale/orders');
    }
    
    public function getArrayStatusClearsale() {
        return $this->status_clearsale;
    }    

}