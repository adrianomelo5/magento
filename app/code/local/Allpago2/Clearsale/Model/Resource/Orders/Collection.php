<?php

class Allpago_Clearsale_Model_Resource_Orders_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('clearsale/orders');
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
