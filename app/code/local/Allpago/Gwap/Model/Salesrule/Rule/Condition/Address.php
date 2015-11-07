<?php

class Allpago_Gwap_Model_Salesrule_Rule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'base_subtotal' => Mage::helper('salesrule')->__('Subtotal'),
            'installments' => Mage::helper('salesrule')->__('Installments'),
            'total_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
            'weight' => Mage::helper('salesrule')->__('Total Weight'),
            'payment_method' => Mage::helper('salesrule')->__('Payment Method'),
            'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
            'postcode' => Mage::helper('salesrule')->__('Shipping Postcode'),
            'region' => Mage::helper('salesrule')->__('Shipping Region'),
            'region_id' => Mage::helper('salesrule')->__('Shipping State/Province'),
            'country_id' => Mage::helper('salesrule')->__('Shipping Country'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal': case 'weight': case 'total_qty': case 'installments':
                return 'numeric';

            case 'shipping_method': case 'payment_method': case 'country_id': case 'region_id':
                return 'select';
        }
        return 'string';
    }

}
