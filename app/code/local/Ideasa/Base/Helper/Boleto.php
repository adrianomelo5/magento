<?php

/**
* 
* SuperEmpreendedor para Magento
* 
* @category     SuperEmpreendedor
* @packages     Base
* @copyright    Copyright (c) 2014 SuperEmpreendedor (http://www.superempreendedor.com/pagseguro)
* @version      1.16.2
* @license      http://www.superempreendedor.com/magento/licenca (Este arquivo Ã© propriedade do SuperEmpreendedor e nÃ£o pode ser copiado ou distribuÃ­do sem autorizaÃ§Ã£o.)
*
*/

class Ideasa_Base_Helper_Boleto extends Mage_Core_Helper_Abstract {

    function feriados($ano, $posicao) {
        $dia = 86400;
        $datas = array();
        $datas['pascoa'] = easter_date($ano);
        $datas['sexta_santa'] = $datas['pascoa'] - (2 * $dia);
        $datas['carnaval'] = $datas['pascoa'] - (47 * $dia);
        $datas['corpus_christi'] = $datas['pascoa'] + (60 * $dia);
        $feriados = array(
            '01/01',
            date('d/m', $datas['carnaval']),
            date('d/m', $datas['sexta_santa']),
            date('d/m', $datas['pascoa']),
            '21/04',
            '01/05',
            date('d/m', $datas['corpus_christi']),
            '12/10',
            '02/11',
            '15/11',
            '25/12'
        );
        return $feriados[$posicao] . "/" . $ano;
    }

    //FORMATA COMO TIMESTAMP
    function dataToTimestamp($data) {
        $ano = substr($data, 6, 4);
        $mes = substr($data, 3, 2);
        $dia = substr($data, 0, 2);
        return mktime(0, 0, 0, $mes, $dia, $ano);
    }

    function soma1dia($data) {
        $ano = substr($data, 6, 4);
        $mes = substr($data, 3, 2);
        $dia = substr($data, 0, 2);
        return date("d/m/Y", mktime(0, 0, 0, $mes, $dia + 1, $ano));
    }

    function somaDiasUteis($xDataInicial, $xSomarDias, $xUsaDiasUteis) {
        if ($xUsaDiasUteis) {
            for ($ii = 1; $ii <= $xSomarDias; $ii++) {
                $xDataInicial = $this->soma1dia($xDataInicial); //SOMA DIA NORMAL
                //VERIFICANDO SE EH DIA DE TRABALHO
                if (date("w", $this->dataToTimestamp($xDataInicial)) == "0") {
                    //SE DIA FOR DOMINGO OU FERIADO, SOMA +1
                    $xDataInicial = $this->soma1dia($xDataInicial);
                } else if (date("w", $this->dataToTimestamp($xDataInicial)) == "6") {
                    //SE DIA FOR SABADO, SOMA +2
                    $xDataInicial = $this->soma1dia($xDataInicial);
                    $xDataInicial = $this->soma1dia($xDataInicial);
                } else {
                    //senao vemos se este dia eh FERIADO
                    for ($i = 0; $i < 11; $i++) {
                        if ($xDataInicial == $this->feriados(date("Y"), $i)) {
                            $xDataInicial = $this->soma1dia($xDataInicial);
                        }
                    }
                }
            }
        } else {
            for ($ii = 1; $ii <= $xSomarDias; $ii++) {
                $xDataInicial = $this->soma1dia($xDataInicial); //SOMA DIA NORMAL
            }
        }
        return $xDataInicial;
    }

    /*
     * Metodo que calcula a data de expiração de um boleto
     * 
     * @param date $validadeBoleto
     * @param int $diasExpiracao
     * @return date
     * 
     */
    public function calculaExpiracao($vencimentoBoleto, $diasExpiracao) {
        //$dataTeste = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        //$dataAtual = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        $dataHoraVencimentoBoleto = new Zend_Date($vencimentoBoleto);
        $dataHoraVencimentoBoleto->setHour(23);
        $dataHoraVencimentoBoleto->setMinute(59);
        $dataHoraVencimentoBoleto->setSecond(59);
        $dataHoraVencimentoBoleto = $dataHoraVencimentoBoleto->get('yyyy-MM-dd HH:mm:ss');

        //Ideasa_Base_Helper_LogUtils::varDump($dataAtual);
        //$soma = ($validade + $diasUteis);
        //$dataVencimentoBoleto = Mage::helper('base/date')->addDaysToUs($soma, $dataAtual);

        return Mage::helper('base/date')->addDaysToUs($diasExpiracao, $dataHoraVencimentoBoleto);
    }

}