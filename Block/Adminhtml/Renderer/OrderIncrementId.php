<?php
class Cammino_Loyalty_Block_Adminhtml_Renderer_OrderIncrementId extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $orderId = $row->getData('value');
        $incrementId = Mage::getModel('sales/order')->load($orderId)->getIncrementId();

        $html = '<tr>
                    <td class="label"><label for="order_id">Nº Pedido <span class="required">*</span></label></td>
                    <td class="value">
                        <input id="order_id" name="order_id" value="'.$incrementId.'" type="text" class=" input-text required-entry" wfd-id="id3">
                    </td>
                </tr>';

    return $html;

    }
}