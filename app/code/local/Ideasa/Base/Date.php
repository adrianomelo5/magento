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

class Ideasa_Base_Date {

    protected $day;
    protected $month;
    protected $year;
    protected $hour;
    protected $minute;
    protected $second;
    
    public function __construct($day, $month, $year, $hour, $minute, $second) {
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
        $this->hour = $hour;
        $this->minute = $minute;
        $this->second = $second;
    }
    
    public static function getInstanceFormatoAmericano($date){
        $ano = substr($date, 0, 4);
        $mes = substr($date, 5, 2);
        $dia = substr($date, 8, 2);
        $hora = substr($date, 11, 2);
        $minuto = substr($date, 14, 2);
        $segundo = substr($date, 17, 2);
        
        if(Ideasa_Base_Helper_StringUtils::isEmpty($hora)){
            $hora = 0;
        }
        if(Ideasa_Base_Helper_StringUtils::isEmpty($minuto)){
            $minuto = 0;
        }
        if(Ideasa_Base_Helper_StringUtils::isEmpty($segundo)){
            $segundo = 0;
        }
        
        return new Ideasa_Base_Date($dia, $mes, $ano, $hora, $minuto, $segundo);
    }

    public function setDay($day) {
        $this->day = $day;
    }

    public function getDay() {
        return $this->day;
    }

    public function setMonth($month) {
        $this->month = $month;
    }

    public function getMonth() {
        return $this->month;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    public function getYear() {
        return $this->year;
    }

    public function setHour($hour) {
        $this->hour = $hour;
    }

    public function getHour() {
        return $this->hour;
    }

    public function setMinute($minute) {
        $this->minute = $minute;
    }

    public function getMinute() {
        return $this->minute;
    }

    public function setSecond($second) {
        $this->second = $second;
    }

    public function getSecond() {
        return $this->second;
    }
}

?>
