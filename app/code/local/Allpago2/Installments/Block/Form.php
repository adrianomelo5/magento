<?php

class Allpago_Installments_Block_Form extends Allpago_Installments_Block_Abstract {

    private $_paymentInstallmentCode;
    private $_paymentFormBlock;

    const CHECKED = 'checked="checked"';
    const SELECTED = 'selected="selected"';

    public function _construct() {
        $this->setTemplate('allpago_installments/form.phtml');
    }

    public function isOnestepActive() {
        return Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
    }

    public function isOnepageActive() {
        return Mage::getStoreConfig('onepagecheckout/general/enabled');
    }

    private function _applyExtraParams() {
        if (!$this->_processedInstallments) {
            $this->_getInstallments();
        }

        $form = $this->getPaymentFormBlock();
        if (!$form) {
            Mage::throwException('Payment form block must be passed as an argument on installment form block creation.');
        }

        $iterable = $this->_getInstallments()->returnIterable();

        foreach ($iterable as &$installment) {
            if ($installment->getValue() == 1) {
                if ($form->getInfoData($this->getPaymentInstallmentCode()) == "" || $form->getInfoData($this->getPaymentInstallmentCode()) == $installment->getValue()) {
                    if ($this->getFormType()) {
                        $installment->setExtraParams(self::CHECKED);
                    } else {
                        $installment->setExtraParams(self::SELECTED);
                    }
                }
            } else {
                if ($form->getInfoData($this->getPaymentInstallmentCode()) == $installment->getValue()) {
                    if ($this->getFormType()) {
                        $installment->setExtraParams(self::CHECKED);
                    } else {
                        $installment->setExtraParams(self::SELECTED);
                    }
                }
            }
        }

        $this->_getInstallments()->updateIterable($iterable);
    }

    /*
     * 
     */

    public function getInstallments($getFromSession = false) {
        $this->_applyExtraParams();
        return $this->_getInstallments()->returnIterable();
    }

    public function getFormType() {
        return Mage::getStoreConfig('allpago/installments/form_type');
    }

}
