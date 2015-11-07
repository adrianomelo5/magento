<?php

class Allpago_Installments_Model_Auto extends Allpago_Installments_Model_Abstract {

    public function _getMaxParcelas() {
        return Mage::getStoreConfig('allpago/installments/n_max_parcelas');
    }

}
