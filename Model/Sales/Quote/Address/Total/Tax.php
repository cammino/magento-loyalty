<?php
class Cammino_Loyalty_Model_Sales_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    protected $_code = 'loyalty_discount';
  
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);

        $helper = Mage::helper("loyalty");

        if (!count($this->_getAddressItems($address)))
            return $this;
        
        if(Mage::helper("loyalty")->hasLoyaltyDiscountApplied()) {
            $discount = -Mage::helper("loyalty")->getLoyaltyDiscount(); //1990
        } else {
            $discount = 0;
        }

        $quote = $address->getQuote($discount);

        if (($discount < 0) && (strpos($helper->getDiscountPaymentMethods(), $quote->getPayment()->getMethodInstance()->getCode()) !== false)) {
            $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
            $subTotal = $totals["subtotal"]->getValue();
            $shipping = 0;
            if(!empty($totals["shipping"])) {
                $shipping = $totals["shipping"]->getValue();
            }
            $subTotal = $subTotal + $shipping;
            $subTotalWithLoyalty = $subTotal + $discount;
            $paymentMethodDiscount = ($helper->getDiscountPercentage() / 100) * $subTotalWithLoyalty;
            $discount = $discount - $paymentMethodDiscount;
            Mage::getSingleton('core/session')->setLoyaltyPaymentMethodDiscount(true);
        } else {
            Mage::getSingleton('core/session')->setLoyaltyPaymentMethodDiscount(false);
        }
        

        $quote->setLoyaltytax($discount);
        $quote->setBaseLoyaltytax($discount);

        $address->setGrandTotal($discount);
        $address->setBaseGrandTotal($discount);

        $address->setWalletDebit($discount);
        $address->setBaseWalletDebit($discount);

        return $this;
    }

  
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $helper = Mage::helper("loyalty");
        if($helper->hasLoyaltyDiscountApplied()) {
            $tax = $address->getWalletDebit();

            $title = '';
            
            if (!empty(Mage::getStoreConfig('loyalty/advanced/show_points'))) {
                $title = 'Resgate de Pontos';
            } else {
                $title = 'Resgate de Créditos';
            }

            if (Mage::getSingleton('core/session')->getLoyaltyPaymentMethodDiscount()) {
                $title = $title . ' ' . $helper->getDiscountTextWithPayment();
            }

            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('checkout')->__($title),
                'value' => $tax
            ));
        }

        return $this;
    }
}