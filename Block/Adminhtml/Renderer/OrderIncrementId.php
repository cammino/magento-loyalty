<?php
class YourNamespace_YourModule_Block_Adminhtml_Renderer_OrderIncrementId extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $orderId = $row->getData($this->getColumn()->getIndex());
        $incrementId = Mage::getModel('sales/order')->load($orderId)->getIncrementId();
        return $incrementId;
    }
}