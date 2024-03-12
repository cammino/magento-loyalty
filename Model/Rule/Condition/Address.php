<?php
class Custom_Module_Model_Rule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address
{

    public function loadAttributeOptions()
    {
        $attributes = array(
            'base_subtotal' => Mage::helper('salesrule')->__('Subtotal'),
            'total_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
            'weight' => Mage::helper('salesrule')->__('Total Weight'),
            'payment_method' => Mage::helper('salesrule')->__('Payment Method'),
            'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
            'postcode' => Mage::helper('salesrule')->__('Shipping Postcode'),
            'region' => Mage::helper('salesrule')->__('Shipping Region'),
            'region_id' => Mage::helper('salesrule')->__('Shipping State/Province'),
            'country_id' => Mage::helper('salesrule')->__('Shipping Country'),
            'loyaltytax' => Mage::helper('salesrule')->__('Loyalty Tax'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    
    public function validate(Varien_Object $object)
    {
        $address = $object;
        if (!$address instanceof Mage_Sales_Model_Quote_Address) {
            if ($object->getQuote()->isVirtual()) {
                $address = $object->getQuote()->getBillingAddress();
            }
            else {
                $address = $object->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && ! $address->hasPaymentMethod()) {
            $address->setPaymentMethod($object->getQuote()->getPayment()->getMethod());
        }

        if ('loyaltytax' == $this->getAttribute()) {
            $address->setLoyaltytax($object->getQuote()->getLoyaltytax());
        }

        return parent::validate($address);
    }

}