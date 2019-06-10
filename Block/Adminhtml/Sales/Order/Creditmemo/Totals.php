<?php
class Cammino_Loyalty_Block_Adminhtml_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
{
    protected function _initTotals() {
        parent::_initTotals();
        $tax = abs(floatval($this->getSource()->getLoyaltytax()));

        if($tax > 0) {
            $this->_totals['loyaltytax'] = new Varien_Object(array(
                'code'      => 'loyaltytax',
                'value'     => $this->getSource()->getLoyaltytax(),
                'base_value'=> $this->getSource()->getBaseLoyaltytax(),
                'label'     => $this->helper('sales')->__('Desconto Loyalty')
            ));
        }
        
        return $this;
    }
}
