<?php

/**
 * Allpago - Gwap Payment Module
 *
 * @title      Magento -> Custom Payment Module for Gwap
 * @category   Payment Gateway
 * @package    Allpago_Gwap
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Gwap_Block_Form_Oneclick extends Mage_Payment_Block_Form {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('allpago_gwap/form/oneclick.phtml');
    }  
    
    public function getCcOptions(){
        $customerId =  Mage::getSingleton('customer/session')->getCustomer()->getId();
        $oneclick = Mage::getModel('gwap/oneclick')->getCollection()->addFieldToFilter('customer_id',$customerId);
        
        $options = array();
        foreach ($oneclick as $data) {
            $options[$data['cc_last4']] = 'xxxx-xxxx-xxxx-'.$data['cc_last4'];
        }
        
        return $options;
    }    

}