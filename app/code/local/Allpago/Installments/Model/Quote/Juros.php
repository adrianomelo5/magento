<?php

class Allpago_Installments_Model_Quote_Juros extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
          $this->setCode('juros');
    }

    /**
     * Collect address subtotal
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if($address->getAddressType() == 'billing'){
            return $this;
        }
        
        $juros = Mage::getSingleton('checkout/session')->getJuros();
        
        $address->setBaseJurosAmount($juros);
        $address->setJurosAmount($juros);
       
        $address->setBaseTotalAmount($this->getCode(), $juros);
        $address->setTotalAmount($this->getCode(), $juros);

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Subtotal
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        //Define as variáveis
        $juros = $address->getJurosAmount();
        //Se existirem juros
        if ((float) $juros > 0) {
            //Para um contabilizador próprio
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('sales')->__('Juros'),
                'value' => $juros
            ));
        }
        $this->_setAddress($address);
        //Retorno padrão
        return array();
    }

    /**
     * Get Subtotal label
     *
     * @return string
     */
    public function getLabel() {
        return Mage::helper('sales')->__('Juros');
    }
}

