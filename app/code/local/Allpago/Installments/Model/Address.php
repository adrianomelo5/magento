<?php

class Allpago_Installments_Model_Address extends Mage_Sales_Model_Quote_Address {


    /**
     * Get all available address items
     *
     * @return array
     */
    public function getAllItems()
    {        
        return parent::getAllItems();
    }
   
}
