<?php

/**
 * Allpago - Braspag Payment Module
 *
 * @title      Magento -> Custom Payment Module for Braspag (Brazil)
 * @category   Payment Gateway
 * @package    Allpago_Braspag
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Model_Mysql4_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('gwap/order', 'id');
    }

    /**
     * Filter by status
     *
     * @param string $status
     * @return Allpago_Mc_Model_Mysql4_Order_Collection
     */
    public function addStatusFilter($status) {
        $this->addFieldToFilter('main_table.status', $status);
        return $this;
    }
    
    /**
     * Filter by status
     *
     * @param string $status
     * @return Allpago_Mc_Model_Mysql4_Order_Collection
     */
    public function addStatusFilterCustom($status1,$status2) {
        $this->addFieldToFilter('main_table.status', array(array('in'=>array($status1,$status2))));
        return $this;
    }    
    
    /**
     * Filter by type
     *
     * @param string $type
     * @return Allpago_Mc_Model_Mysql4_Order_Collection
     */
    public function addTypeFilter($type) {
        $this->addFieldToFilter('main_table.type', $type);
        return $this;
    }

    /**
     * Filter by abandoned status
     *
     * @param integer $abandoned
     * @return Allpago_Mc_Model_Mysql4_Order_Collection
     */
    public function addAbandonedFilter($abandoned) {
        $this->addFieldToFilter('main_table.abandoned', $abandoned);
        return $this;
    }

    
    /**
     * Filter Time  
     *
     * @param integer $time
     * @return Allpago_Mc_Model_Resource_Payment_Collection
     */
    public function addExpireFilter( $time ) {
       
        $this->addFieldToFilter('main_table.created_at',  array('to'=> date("Y-m-d H:i:s", $time )) );
        return $this;
    }
}

