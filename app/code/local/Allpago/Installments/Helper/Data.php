<?php

class Allpago_Installments_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getInstallmentModel()
    {
       
        $mode = Mage::getStoreConfig('allpago/installments/active');

        $instance = Mage::getModel('installments/'.$mode);
        if( ! ($instance instanceof  Allpago_Installments_Model_Abstract ) ){
            Mage::throwException( printf( Mage::helper('installments')->__(  'Erro ao instanciar model: %s'), $mode ) ) ;
        }

        return $instance;
    }
}
