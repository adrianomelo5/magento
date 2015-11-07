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
class Allpago_Mc_Block_Adminhtml_Log extends Mage_Payment_Block_Form {

    public function _construct() {
        parent::_construct();
        $this->setTemplate('allpago_mc/log.phtml');
    }

    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function getLog() {
        
        if( $this->getOrder() )
        return Mage::getModel('allpago_mc/log')->getCollection()->addOrderFilter($this->getOrder()->getId())->setOrder('id', 'DESC');
    }

}