<?php

class Allpago_Installments_Model_Order_Juros extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    // Collect the totals for the invoice
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $baseMyTotal = "";
        $order = $invoice->getOrder();
        $myTotal = $order->getJurosAmount();
        
        $baseMyTotal = $order->getBaseJurosAmount();

        $invoice->setGrandTotal($invoice->getGrandTotal() + $myTotal);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseMyTotal);

        return $this; 
    }


}
