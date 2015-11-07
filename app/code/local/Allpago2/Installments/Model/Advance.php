<?php

class Allpago_Installments_Model_Advance extends Allpago_Installments_Model_Abstract {


    const LT = 'lt';
    const LTEQ = 'lteq';

    const GT = 'gt';
    const GTEQ = 'gteq';

    const CORINGA = '*';

    
    /*
     * Constants from the big named
     * Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange
     * class
     */

    public function getRangeFinalCode() {
        return Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange::RANGE_FINAL;
    }

    public function getNumeroDeParcelasCode() {
        return Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange::NUMERO_PARCELAS;
    }

    public function getCondicaoMaiorCode() {
        return Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange::CONDICAO_MAIOR;
    }

    public function getCondicaoMenorCode() {
        return Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange::CONDICAO_MENOR;
    }

    public function getRangeInicialCode() {
        return Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange::RANGE_INICIAL;
    }

    private function _pushRangeArray($configArray) {
        $parcelas = array();
        $last = array();

        $capital = $this->getValue();

        foreach ($configArray as $installmentRange) {
            $boolResult = false;

            switch ($installmentRange[$this->getCondicaoMaiorCode()]) {
                case self::GT:
                    $higher = ($capital > $installmentRange[$this->getRangeInicialCode()]
                            || $installmentRange[$this->getRangeInicialCode()] == self::CORINGA );
                    break;

                case self::GTEQ:
                default:
                    $higher = ($capital >= $installmentRange[$this->getRangeInicialCode()]
                            || $installmentRange[$this->getRangeInicialCode()] == self::CORINGA );
                    break;
            }

            switch ($installmentRange[$this->getCondicaoMenorCode()]) {
                case self::LT:
                    $lower = ($capital < $installmentRange[$this->getRangeFinalCode()]
                            || $installmentRange[$this->getRangeFinalCode()] == self::CORINGA );
                    break;

                case self::LTEQ:
                default:
                    $lower = ($capital <= $installmentRange[$this->getRangeFinalCode()]
                            || $installmentRange[$this->getRangeFinalCode()] == self::CORINGA );
                    break;
            }

            $boolResult = $higher && $lower;

            if ($boolResult) {
                array_push($parcelas, $installmentRange[$this->getNumeroDeParcelasCode()]);
            }

            $last = $installmentRange;
        }

        if (count($parcelas) == 0) {
            if (is_array($last)) {
                if (!array_key_exists($this->getNumeroDeParcelasCode(), $last)) {
                    Mage::throwException('No higher installment range specified.');
                }
            }
            array_push($parcelas, 1);
        }

        return $parcelas;
    }

    public function _getMaxParcelas() {
        $configArray = $this->getParcelaConfigurationArray();
        if (count($configArray) < 1) {
            Mage::throwException('No installment range specified.');
        }

        $parcelas = $this->_pushRangeArray($configArray);

        sort($parcelas);

        if (array_key_exists(0, $parcelas)) {
            return $parcelas[0];
        }

        return $parcelas;
    }

   
}
