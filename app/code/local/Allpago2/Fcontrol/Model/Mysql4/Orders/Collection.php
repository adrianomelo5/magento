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
class Allpago_Fcontrol_Model_Mysql4_Orders_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('fcontrol/orders', 'id');
    }

    /**
     * Filter by status
     *
     * @param string $status
     * @return Allpago_Fcontrol_Model_Mysql4_Orders_Collection
     */
    public function addStatusFilter($status) {
        $this->addFieldToFilter('main_table.status', $status);
        return $this;
    }

    /**
     * Filter by abandoned
     *
     * @param int $abandoned
     * @return Allpago_Fcontrol_Model_Mysql4_Orders_Collection
     */
    public function addAbandonedFilter($abandoned) {
        $this->addFieldToFilter('main_table.abandoned', $abandoned);
        return $this;
    }
    
    /**
     * Filter Time  
     *
     * @param integer $time
     * @return Allpago_Fcontrol_Model_Mysql4_Orders_Collection
     */
    public function addTimeFilter( $time ) {
        if( !$time || $time < 0 ){
            $time = 1;
        }
        
        $this->addFieldToFilter('main_table.updated_at',  array('to'=>date("Y-m-d H:i:s", strtotime("-{$time} hours") ) ) );
        return $this;
    }    

}

