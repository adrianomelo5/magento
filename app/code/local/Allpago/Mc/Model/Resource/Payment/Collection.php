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
class Allpago_Mc_Model_Resource_Payment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('allpago_mc/payment', 'id');
    }

    /**
     * Filter by status
     *
     * @param string $status
     * @return Allpago_Mc_Model_Resource_Payment_Collection
     */
    public function addStatusFilter($status) {
        $this->addFieldToFilter('main_table.status', $status);
        return $this;
    }

    /**
     * Filter by abandoned status
     *
     * @param integer $abandoned
     * @return Allpago_Mc_Model_Resource_Payment_Collection
     */
    public function addAbandonedFilter($abandoned) {
        $this->addFieldToFilter('main_table.abandoned', $abandoned);
        return $this;
    }
    /**
     * Filter by type 
     *
     * @param integer $type
     * @return Allpago_Mc_Model_Resource_Payment_Collection
     */
    public function addTypeFilter($type1,$type2 = null, $type3 = null) {
        
        if($type3){
            $this->addFieldToFilter('main_table.type', array(  
                array('attribute'=>'main_table.type','eq'=>$type1),            
                array('attribute'=>'main_table.type','eq'=>$type2),
                array('attribute'=>'main_table.type','eq'=>$type3) ));            
        }elseif($type2){
            $this->addFieldToFilter('main_table.type', array(  
                array('attribute'=>'main_table.type','eq'=>$type1),            
                array('attribute'=>'main_table.type','eq'=>$type2) ));             
        }else{
            $this->addFieldToFilter('main_table.type', $type1);
        }
        
        return $this;
    }
    /**
     * Filter Time  
     *
     * @param integer $time
     * @return Allpago_Mc_Model_Resource_Payment_Collection
     */
    public function addTimeFilter() {
        $time = Mage::getStoreConfig('allpago/allpago_mc/tempo_espera');
        if( !$time || $time < 0 ){
            $time = 1;
        }
        
        $this->addFieldToFilter('main_table.updated_at',  array('to'=>Mage::getModel('core/date')->date("Y-m-d H:i:s", strtotime("-{$time} hours") ) ) );
        return $this;
    }

}

