<?php
class Correio_Shipping_Model_Carrier_CorreioPost  
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'correiopost';

    protected $_result = null;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        
        
        if (!$this->getConfigFlag('active'))
        {
            //Desabilitado
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));

        $packagevalue = $request->getBaseCurrency()->convert($request->getPackageValue(), $request->getPackageCurrency());

        $frompcode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
        $topcode = $request->getDestPostcode();

//        if(!preg_match("/^[0-9]{8}$/", $topcode))
//        {
//            //CEP está errado
//            $error->setErrorMessage('O CEP está errado');
//            $result->append($error);
//            Mage::helper('customer')->__('Invalid ZIP CODE');
//            return $result;
//        }
//die('dfgf');
        $sweight = $request->getPackageWeight();

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);

        $method->setCarrierTitle($this->getConfigData('name'));

        $method->setMethod('sedex');

        $method->setMethodTitle('Sedex');

        $method->setPrice(10 + $this->getConfigData('handling_fee'));

        $method->setCost(10);

            $result->append($method);

        $this->_result = $result;

        $this->_updateFreeMethodQuote($request);

        return $this->_result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('title'));
    }
}

