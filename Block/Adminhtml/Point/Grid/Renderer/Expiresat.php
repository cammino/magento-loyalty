<?php
class Cammino_Loyalty_Block_Adminhtml_Point_Grid_Renderer_Expiresat extends
Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
	public function render(Varien_Object $row)
	{
		$value = $row->getData('expires_at');
        return Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString('dd/MM/YYYY HH:mm:ss');
	}
}