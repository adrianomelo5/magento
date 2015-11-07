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

class Ideasa_Base_Helper_Data extends Mage_Core_Helper_Abstract {

  /**
   * Verifica se o método de pagamento consta na lista de Payment Methods desenvolvidos pelo Idea SA.
   * 
   * @return type boolean
   */
  public function isIdeasaPaymentMethod($paymentMethodCode) {
    return in_array($paymentMethodCode, Ideasa_Base_Config::listPaymentMethods());
  }

  /**
   * Retorna a lista com os métodos de pagamentos
   * 
   * @return type array
   */
  public function listPaymentMethods() {
    return Ideasa_Base_Config::listPaymentMethods();
  }

  public function isOscCssEnabled() {
    return Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::OSC_CSS_ACTIVE);
  }

  public function canSendEmail(Mage_Sales_Model_Order $order) {
    if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
      if (Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::CONTROLE_ENVIO_EMAIL_CANCELADO)) {
        return true;
      }
    }
    if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
      if (Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::CONTROLE_ENVIO_EMAIL_NOVO)) {
        return true;
      }
    }
    if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING || $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
      if (Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::CONTROLE_ENVIO_EMAIL_FATURA_GERADA)) {
        return true;
      }
    }
    return false;
  }

  public function isUrlTelevendas() {
    $currentUrl = Mage::helper('core/url')->getCurrentUrl();
    if (preg_match('/ipgtelevendas/', $currentUrl)) {
      return true;
    }
    return false;
  }

  public function addStatusHistoryComment($order, $message, $notified = false) {
    $order->addStatusHistoryComment($message, false)->setIsCustomerNotified($notified);
    $order->save();
  }

  public function getStoreConfig($value, $store = null) {
    return trim(Mage::getStoreConfig($value, $store));
  }

  public function getPaymentMethodCode() {
    $order = Mage::helper('base/session')->getCurrentOrder();
    $payment = $order->getPayment();
    return $payment->getMethodInstance()->getCode();
  }

  /**
   * Retorna apenas os números.
   * 
   */
  public function getOnlyNumbers($entry) {
    return preg_replace('/\D/', '', $entry);
  }

}
