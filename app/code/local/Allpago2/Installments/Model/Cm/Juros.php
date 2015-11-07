<?php

class Allpago_Installments_Model_Cm_Juros extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Creditmemo $invoice)
    {
        $order = $invoice->getOrder();
        $myTotal = $order->getJurosAmount();
        $baseMyTotal = $order->getBaseJurosAmount();

        $invoice->setGrandTotal($invoice->getGrandTotal() + $myTotal);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseMyTotal);

        return $this;
    }

}
