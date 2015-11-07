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
class Allpago_Fcontrol_Model_Mysql4_Orders extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('fcontrol/orders', 'id');
    }

}