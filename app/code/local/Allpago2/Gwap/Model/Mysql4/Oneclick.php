<?php

/**
 * Allpago - Gwap Payment Module
 *
 * @title      Magento -> Custom Payment Module for Braspag (Brazil)
 * @category   Payment Gateway
 * @package    Allpago_Gwap
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Model_Mysql4_Oneclick extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('gwap/oneclick', 'id');
    }

}