<?php

/**
 * Allpago + Conversão Module
 *
 * @title      Magento -> + Conversão Module
 * @category   Payment Gateway
 * @package    Allpago_Mc
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
class Allpago_Mc_Model_Resource_Log_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('allpago_mc/log', 'id');
    }

    /**
     * Filter by Order
     *
     * @param integer $order
     * @return Allpago_Mc_Model_Resource_Log_Collection
     */
    public function addOrderFilter($order) {
        $this->addFieldToFilter('main_table.order_id', $order);
        return $this;
    }

    /**
     * Filter by Robot
     *
     * @param string $robot
     * @return Allpago_Mc_Model_Resource_Log_Collection
     */
    public function addRobotFilter($robot) {
        $this->addFieldToFilter('main_table.robot', $robot);
        return $this;
    }

}

