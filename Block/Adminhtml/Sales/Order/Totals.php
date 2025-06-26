<?php
class Cammino_Loyalty_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    protected function _initTotals() {
        parent::_initTotals();
        if (empty(Mage::getStoreConfig('loyalty/general/active'))) {
            return $this;
        }

        $order = $this->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod == 'loyalty') {
            $this->_totals['total_loyalty'] = new Varien_Object(array(
                'code' => 'total_loyalty',
                'value' => ($order->getSubtotal() + $order->getShippingAmount() + $order->getTaxAmount() - $order->getDiscountAmount()) * -1,
                'base_value' => ($order->getSubtotal() + $order->getShippingAmount() + $order->getTaxAmount() - $order->getDiscountAmount()) * -1,
                'label' => $this->helper('sales')->__('Desconto Loyalty')
            ));
        }
        
        $tax = abs(floatval($this->getSource()->getLoyaltytax()));
        if($tax > 0) {
            if (strpos($this->getOrder()->getPayment()->getMethodInstance()->getCode(), 'pix') !== false) {
                $debit = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $this->getOrder()->getId())
                ->addFieldToFilter('direction', 'debit')
                ->getFirstItem();
                $pointsUsed = $debit->getPoints() * -1;
                $pointToMoney = $debit->getPointToMoney();
                $loyaltyTax = $pointsUsed / $pointToMoney;
                $pixDiscount = $tax - $loyaltyTax;
                $this->_totals['loyaltytax'] = new Varien_Object(array(
                    'code'      => 'loyaltytax',
                    'value'     => $loyaltyTax * -1,
                    'base_value'=> $loyaltyTax * -1,
                    'label'     => $this->helper('sales')->__('Desconto Loyalty')
                ));
                if ($pixDiscount > 0) {
                    $this->_totals['pixdiscount'] = new Varien_Object(array(
                        'code'      => 'pixdiscount',
                        'value'     => $pixDiscount * -1,
                        'base_value'=> $pixDiscount * -1,
                        'label'     => 'Desconto PIX'
                    ));
                }
            } else {
                $this->_totals['loyaltytax'] = new Varien_Object(array(
                    'code'      => 'loyaltytax',
                    'value'     => $this->getSource()->getLoyaltytax(),
                    'base_value'=> $this->getSource()->getBaseLoyaltytax(),
                    'label'     => $this->helper('sales')->__('Desconto Loyalty')
                ));
            }
        }
        return $this;
    }
}
