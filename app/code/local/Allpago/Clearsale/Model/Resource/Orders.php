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
class Allpago_Clearsale_Model_Resource_Orders extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct() {
        $this->_init('clearsale/orders', 'id');
    }

}