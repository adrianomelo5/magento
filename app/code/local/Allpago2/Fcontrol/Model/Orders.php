<?php

/**
 * Allpago Module for Fcontrol
 *
 * @title      Magento -> Custom Module for Fcontrol
 * @category   Fraud Control Gateway
 * @package    Allpago_Fcontrol
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
class Allpago_Fcontrol_Model_Orders extends Mage_Core_Model_Abstract {
    const STATUS_APPROVED = 'approved';
    const STATUS_CAPTUREPAYMENT = 'capture payment';
    const STATUS_CREATED = 'created';
    const STATUS_DENIED = 'denied';
    const STATUS_ERROR = 'error';
    const STATUS_FCONTROL = 'fcontrol';
    const STATUS_MAXTRIES = 'max tries';
    const STATUS_PROCESSING = 'processing';
    const STATUS_QUEUED = 'queued';

    public function _construct() {
        $this->_init('fcontrol/orders');
    }

}