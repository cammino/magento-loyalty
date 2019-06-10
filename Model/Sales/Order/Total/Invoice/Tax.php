<?php 
class Cammino_Loyalty_Model_Sales_Order_Total_Invoice_Tax extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect invoice tax amount
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Invoice_Total_Tax
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $discount = $order->getLoyaltytax();

        $invoice->setLoyaltytax($discount);
        $invoice->setBaseLoyaltytax($discount);
        
        $invoice->setGrandTotal($invoice->getGrandTotal() + $discount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $discount);
        return $this;
    }
}
