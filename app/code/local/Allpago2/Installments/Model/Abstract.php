<?php

class Allpago_Installments_Model_Abstract extends Varien_Object {

    
    private $_value;
    private $_installmentsArray;
     
    public function __construct() {
        parent::__construct();
        $this->setMaxInstallment($this->_getMaxParcelas());
        $this->setTaxaJuros($this->_getTaxaJuros()); 
    }
    public function isActive() {
        return Mage::getStoreConfig('allpago/installments/active') ? true : false;
    }

    /*
     * System->Configuration getters
     */

    public function getMessage($juros) {
        if ($juros > 0) {
            return sprintf(Mage::getStoreConfig('allpago/installments/mensagem_padrao_comjuros'), number_format($juros, 2) . '%' );
        } else {
            return Mage::getStoreConfig('allpago/installments/mensagem_padrao_semjuros');
        }
    }

    public function getParcelaConfigurationArray() {
        if (!$this->_installmentsArray) {
            $this->_installmentsArray = unserialize(Mage::getStoreConfig('allpago/installments/parcelas'));
        }
        return $this->_installmentsArray;
    }
    
    
    public function getParcelaMinima() {
        return Mage::getStoreConfig('allpago/installments/parcela_minima');
    }
    
    public function getParcelaSemJuros() {
        return Mage::getStoreConfig('allpago/installments/n_parcelas_s_juros');
    }
    
    public function _getTaxaJuros() {
        return Mage::getStoreConfig('allpago/installments/taxa_juros');
    }
    
    public function setValue($value) {
        $this->_value = $value;
        return $this;
    }

    public function getValue() {
        if (!$this->_value) {
            if( Mage::getSingleton('checkout/session')->getBaseTotal() ){
                $this->_value = Mage::getSingleton('checkout/session')->getBaseTotal();
            }else{
                $this->_value = Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal();
                Mage::getSingleton('checkout/session')->setBaseTotal( Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal() );
            }
            
        }
        return $this->_value;
    }
    
     public function getInstallmentSequence() {

        //$cacheName = 'allpagoInstallments-'.$this->getTaxaJuros().'-'.$this->getValue().'-'.$this->getMaxInstallment().'-'.$this->getParcelaMinima().'-'.$this->getParcelaSemJuros().'-'.$this->getMessage(0).'-'.$this->getMessage(1).Mage::helper('core')->currency($this->getValue());
        //$cache = Mage::getSingleton('core/cache');
        //if ($cachedObject = $cache->load($cacheName)) {
        //    return unserialize($cachedObject);
        //}
        
        $installments = Mage::getModel('installments/installmentset');

        if ($this->getValue() <= 0) {
            return $installments;
        }
        
        $juros = $this->getTaxaJuros() / 100;
        $capital = $this->getValue();
        $capitalJuros = $capital;

        $max = $this->getMaxInstallment();  
        $min = $this->getParcelaMinima();   // 5
        
        for ($parcela = 1; $parcela <= $max; $parcela++) {
            
            if ($parcela == 1) {
                $installments->pushInstallment($capital, $this->getMessage(0));
            } else {
                
                //Define se a parcela será com ou sem juros
                if ($juros > 0 && ($parcela) > $this->getParcelaSemJuros()) {
                    $divided = ($capital) * ( ($juros) / ( 1 - (1 / pow( (1+$juros), $parcela ))));
                    $jurosMessage = $this->getTaxaJuros();
                } else {
                    $divided = $capital / $parcela;
                    $jurosMessage = '0';
                  
                }

                if ($divided >= floatval($min)) {
                    $installments->pushInstallment($divided,  $this->getMessage($jurosMessage), $parcela);
                }
                 
                if ($parcela == Mage::getStoreConfig('allpago/installments/n_parcelas_s_juros') &&
                        Mage::getStoreConfig('allpago/installments/active_installment_type') == 'semjuros' &&
                            Mage::app()->getRequest()->getModuleName() != 'onestepcheckout' &&
                                !$this->getHasInstallment() ) {
                    break;
                }
                
            }
        }

        //$cache->save(serialize($installments),$cacheName,array('allpagoInstallmentsCache'));

        return $installments;
    }

    public function getInstallmentHighest() {
        $installments = $this->getInstallmentSequence();
        $last = false;

        foreach ($installments->returnIterable() as $installment) {
            $last = $installment;
        }

        return $last;
    }

    public function getInstallmentByItem( $parcela ) {
        $installments = $this->getInstallmentSequence();
        $last = false;

        foreach ($installments->returnIterable() as $installment) {
            $last = $installment;
            if( $installment->getValue() == $parcela ){
                break;
            }
        }

        return $last;
    }

}