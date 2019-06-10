<?php
class Cammino_Loyalty_Model_Sales_Order_Total_Creditmemo_Tax extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $loyaltytax = $order->getLoyaltytax();
        $baseLoyaltytax = $order->getBaseLoyaltytax();

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $loyaltytax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseLoyaltytax);
        $creditmemo->setLoyaltytax($loyaltytax);
        $creditmemo->setBaseLoyaltytax($baseLoyaltytax);

        return $this;
    }
}
