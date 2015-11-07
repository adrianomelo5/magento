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

class Ideasa_Base_Block_Adminhtml_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals {

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals() {
        parent::_initTotals();
        $order = $this->getOrder();
        $payment = $order->getPayment();
        $paymentMethodCode = $payment->getMethodInstance()->getCode();

        if (Mage::helper('base')->isIdeasaPaymentMethod($paymentMethodCode)) {
            $amount = Mage::getModel($paymentMethodCode . '/discount')->getIdeasaDiscount($order);
            if (abs($amount) > 0) {
                $baseAmount = Mage::getModel($paymentMethodCode . '/discount')->getIdeasaBaseDiscount($order);
                $code = Mage::getModel($paymentMethodCode . '/discount')->getIdeasaDiscountCode();
                $this->addTotal(new Varien_Object(array(
                            'code' => $code,
                            'value' => $amount,
                            'base_value' => $baseAmount,
                            'label' => Mage::helper($paymentMethodCode)->__('Payment Discount'),
                        )));
            }
        }
        return $this;
    }

}