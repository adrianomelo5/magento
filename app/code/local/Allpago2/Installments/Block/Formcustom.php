<?php

class Allpago_Installments_Block_Formcustom extends Allpago_Installments_Block_Abstract {

    public function _construct() {
        $this->setTemplate('allpago_installments/formcustom.phtml');
    }

    public function isOnestepActive() {
        return Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
    }

    public function isOnepageActive() {
        return Mage::getStoreConfig('onepagecheckout/general/enabled');
    }

    public function getInstallments($getFromSession = false) {
        return $this->_getInstallments()->returnIterable();
    }

    public function getFormType() {
        return Mage::getStoreConfig('allpago/installments/form_type');
    }

}
