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

class Ideasa_Base_Block_Checkout_Onepage_Success extends Mage_Checkout_Block_Onepage_Success {

  private $paymentMethod;
  private $isIdeasaMethod;

  protected function _construct() {
    parent::_construct();
    $this->setPaymentMethod();

    $this->isIdeasaMethod = Mage::helper('base')->isIdeasaPaymentMethod($this->paymentMethod);
    if ($this->isIdeasaMethod) {
      $this->setTemplate('ideasa/' . $this->paymentMethod . '/checkout/success-details.phtml');
    }
  }

  public function getBlock() {
    if ($this->isIdeasaMethod) {
      $block = $this->getLayout()->getBlockSingleton($this->paymentMethod . '/checkout_onepage_success');
      if ($block) {
        return $block;
      }
    }
    return null;
  }

  private function setPaymentMethod() {
    $order = Mage::helper('base/session')->getCurrentOrder();
    $payment = $order->getPayment();
    $method = $payment->getMethodInstance();
    $this->paymentMethod = $payment->getMethodInstance()->getCode();
  }

}
