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

class Ideasa_Base_PaymentDiscount extends stdClass {

    /**
     *
     * @var type 
     */
    public $baseCurrencyCode;

    /**
     *
     * @var type 
     */
    public $totalPercent;

    /**
     *
     * @var type
     */
    public $baseSubtotalWithDiscount;

    /**
     *
     * @var type
     */
    public $baseTax;

    public function __construct() {
        $this->baseCurrencyCode = 0;
        $this->totalPercent = 0;
        $this->baseSubtotalWithDiscount = 0;
        $this->baseTax = 0;
    }

    public static function getInstance($baseCurrencyCode, $totalPercent, $baseSubtotalWithDiscount, $baseTax) {
        $paymentDiscount = new Ideasa_Base_PaymentDiscount();
        $paymentDiscount->setBaseCurrencyCode($baseCurrencyCode);
        $paymentDiscount->setTotalPercent($totalPercent);
        $paymentDiscount->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
        $paymentDiscount->setBaseTax($baseTax);

        return $paymentDiscount;
    }

    public function getTotalPercent() {
        return $this->totalPercent;
    }

    public function setTotalPercent($totalPercent) {
        $this->totalPercent = $totalPercent;
    }

    public function getBaseCurrencyCode() {
        return $this->baseCurrencyCode;
    }

    public function setBaseCurrencyCode($baseCurrencyCode) {
        $this->baseCurrencyCode = $baseCurrencyCode;
    }

    public function getBaseSubtotalWithDiscount() {
        return $this->baseSubtotalWithDiscount;
    }

    public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount) {
        $this->baseSubtotalWithDiscount = $baseSubtotalWithDiscount;
    }

    public function getBaseTax() {
        return $this->baseTax;
    }

    public function setBaseTax($baseTax) {
        $this->baseTax = $baseTax;
    }

}