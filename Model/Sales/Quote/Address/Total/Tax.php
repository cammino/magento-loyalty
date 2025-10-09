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

        $appliedRuleIds = $quote->getAppliedRuleIds();

//        $allPercentDiscounts = [];
        $allDiscountsValues = [];
        foreach ($quote->getAllItems() as $item) {
            $discountAmount = $item->getDiscountAmount();
            $allDiscountsValues[] = $discountAmount;
//            $appliedRuleIds = explode(',', $item->getAppliedRuleIds());
//            foreach ($appliedRuleIds as $ruleId) {
//                if (!$ruleId) continue;
//                if (!isset($allPercentDiscounts[$ruleId])) {
//                    $rule = Mage::getModel('salesrule/rule')->load($ruleId);
//                    if ($rule->getSimpleAction() == Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION) {
//                        $allPercentDiscounts[$ruleId] = $rule->getDiscountAmount();
//                    }
//                }
//            }
        }

        if (($discount < 0) && (strpos($helper->getDiscountPaymentMethods(), $quote->getPayment()->getMethodInstance()->getCode()) !== false)) {
            $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
            $subTotal = $totals["subtotal"]->getValue();
            $shipping = 0;
            if(!empty($totals["shipping"])) {
                $shipping = $totals["shipping"]->getValue();
            }
            $subTotal = $subTotal + $shipping;
            Mage::log('subTotal: ' . $subTotal, null, 'loyalty_discount.log');
            $subTotalWithLoyalty = $subTotal + $discount;
            Mage::log('loyalty: ' . $discount, null, 'loyalty_discount.log');
            Mage::log('subTotalWithLoyalty: ' . $subTotalWithLoyalty, null, 'loyalty_discount.log');
//            foreach ($allPercentDiscounts as $pctD) {
//                $subTotalWithLoyaltyantigo = number_format(($subTotalWithLoyalty - (($pctD / 100) * $subTotal)), 2);
//            }
            $totalDiscountValue = 0;
            foreach ($allDiscountsValues as $discountedAmount) {
                $totalDiscountValue = $totalDiscountValue + $discountedAmount;
            }
            Mage::log('totalDiscountValue: ' . $totalDiscountValue, null, 'loyalty_discount.log');
            $subTotalWithLoyalty = $subTotalWithLoyalty - $totalDiscountValue;
            Mage::log('subTotalWithLoyalty+totaldiscount: ' . $subTotalWithLoyalty, null, 'loyalty_discount.log');
            Mage::log('$helper->getDiscountPercentage(): ' . $helper->getDiscountPercentage(), null, 'loyalty_discount.log');
            $paymentMethodDiscount = ($helper->getDiscountPercentage() / 100) * $subTotalWithLoyalty;
            Mage::log('desconto da forma de pagamento então fica: ' . $paymentMethodDiscount, null, 'loyalty_discount.log');
            $discount = $discount - $paymentMethodDiscount;
        } else {
            Mage::getSingleton('core/session')->setLoyaltyPaymentMethodDiscount(false);
        }
        Mage::log('desconto loyalty final: ' . $discount, null, 'loyalty_discount.log');
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