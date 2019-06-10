<?php
class Cammino_Loyalty_Model_Sales_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    protected $_code = 'loyalty_discount';
  
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);

        if (!count($this->_getAddressItems($address)))
            return $this;
        
        if(Mage::helper("loyalty")->hasLoyaltyDiscountApplied()) {
            $discount = -Mage::helper("loyalty")->getLoyaltyDiscount();
        } else {
            $discount = 0;
        }

        $quote = $address->getQuote($discount);
        $quote->setLoyaltytax($discount);
        $quote->setBaseLoyaltytax($discount);

        $address->setGrandTotal($discount);
        $address->setBaseGrandTotal($discount);

        $address->setWalletDebit($discount);
        $address->setBaseWalletDebit($discount);

        return $this;
    }

  
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        if(Mage::helper("loyalty")->hasLoyaltyDiscountApplied()) {
            $tax = $address->getWalletDebit();

            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('checkout')->__('Resgate de Pontos'),
                'value' => $tax
            ));
        }

        return $this;
    }
}