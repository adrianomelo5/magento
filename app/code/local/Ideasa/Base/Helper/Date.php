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

class Ideasa_Base_Helper_Date extends Mage_Core_Helper_Abstract {

    private $logger;

    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->logger = Ideasa_Base_Logger::getLogger(__CLASS__);
    }

    public function addMinutes($minutesToAdd) {
        return gmdate("Y-m-d H:i:s", mktime(date("H"), date("i") + $minutesToAdd, date("s"), date("m"), date("d"), date("Y")));
    }

    public function addMinutesToUs($minutesToAdd, $dataInicial = null) {
        if ($dataInicial == null) {
            $dataInicial = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        }
        $data = Ideasa_Base_Date::getInstanceFormatoAmericano($dataInicial);

        return gmdate("Y-m-d H:i:s", mktime($data->getHour(), $data->getMinute() + $minutesToAdd, $data->getSecond(), $data->getMonth(), $data->getDay(), $data->getYear()));
    }

    public function addHours($hoursToAdd) {
        return gmdate("Y-m-d H:i:s", mktime(date("H") + $hoursToAdd, date("i"), date("s"), date("m"), date("d"), date("Y")));
    }

    public function addHoursToUs($hoursToAdd, $dataInicial) {
        if ($dataInicial == null) {
            $dataInicial = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        }

        $data = Ideasa_Base_Date::getInstanceFormatoAmericano($dataInicial);

        return gmdate("Y-m-d H:i:s", mktime($data->getHour() + $hoursToAdd, $data->getMinute(), $data->getSecond(), $data->getMonth(), $data->getDay(), $data->getYear()));
    }

    public function addDays($daysToAdd, $dataInicial) {
        if ($dataInicial == null) {
            // FIXME: o formato da data deve ser Y-m-d H:i:s
            $dataInicial = Mage::getModel('core/date')->date('d/m/Y H:i:s');
        }
        $ano = substr($dataInicial, 6, 4);
        $mes = substr($dataInicial, 3, 2);
        $dia = substr($dataInicial, 0, 2);
        $hora = substr($dataInicial, 11, 2);
        $minuto = substr($dataInicial, 14, 2);
        $segundo = substr($dataInicial, 17, 2);

        return gmdate("Y-m-d H:i:s", mktime($hora, $minuto, $segundo, $mes, $dia + $daysToAdd, $ano));
    }

    /**
     * Adiciona dias em uma data para formato Americano.
     * 
     * @param type $daysToAdd
     * @param type $dataInicial
     * @return type
     */
    public function addDaysToUs($daysToAdd, $dataInicial) {
        if ($dataInicial == null) {
            $dataInicial = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        }
        $data = Ideasa_Base_Date::getInstanceFormatoAmericano($dataInicial);
        
        return gmdate("Y-m-d H:i:s", mktime($data->getHour(), $data->getMinute(), $data->getSecond(), $data->getMonth(), $data->getDay() + $daysToAdd, $data->getYear()));
    }

    public function isUsDateBeforeToday($date) {
        if ($date == null) {
            return false;
        }

        $ano = substr($date, 0, 4);
        $mes = substr($date, 5, 2);
        $dia = substr($date, 8, 2);
        $hora = substr($date, 11, 2);
        $minuto = substr($date, 14, 2);
        $segundo = substr($date, 17, 2);

        $date = mktime($hora, $minuto, $segundo, $mes, $dia, $ano);
        $agora = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

        if ($date < $agora) {
            return true;
        }
        return false;
    }

    public function isDateBeforeAnother($date, $anotherDate) {
        if ($date == null || $anotherDate == null) {
            return false;
        }

        $ano = substr($date, 0, 4);
        $mes = substr($date, 5, 2);
        $dia = substr($date, 8, 2);
        $hora = substr($date, 11, 2);
        $minuto = substr($date, 14, 2);
        $segundo = substr($date, 17, 2);
        $date = mktime($hora, $minuto, $segundo, $mes, $dia, $ano);

        $ano = substr($anotherDate, 0, 4);
        $mes = substr($anotherDate, 5, 2);
        $dia = substr($anotherDate, 8, 2);
        $hora = substr($anotherDate, 11, 2);
        $minuto = substr($anotherDate, 14, 2);
        $segundo = substr($anotherDate, 17, 2);
        $anotherDate = mktime($hora, $minuto, $segundo, $mes, $dia, $ano);

        if ($date < $anotherDate) {
            return true;
        }

        return false;
    }

    /**
     * Converte data no formato 00/00/0000 00:00:00 (dd/mm/yyyy HH:MM:SS)
     * 
     * @param type $data
     * @return type
     */
    public function convertDateToBr($data) {
        return implode(!strstr($data, '/') ? "/" : "-", array_reverse(explode(!strstr($data, '/') ? "-" : "/", $data)));
    }

    public function isBrValidDate($data) {
        $data = split("[-,/]", $data);
        if (!checkdate($data[1], $data[0], $data[2]) and !checkdate($data[1], $data[2], $data[0])) {
            return false;
        }

        return true;
    }

}

?>
