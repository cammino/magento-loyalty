<?php
class Cammino_Loyalty_Block_Adminhtml_Renderer_OrderLink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $orderIncrementId = $row->getData($this->getColumn()->getIndex());
        $orderUrl = $this->getUrl('adminhtml/sales_order/view', array('order_id' => Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId)->getId()));
        return '<a href="' . $orderUrl . '" target="_blank">' . $orderIncrementId . '</a>';
    }
}