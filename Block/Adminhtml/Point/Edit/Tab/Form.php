<?php
class Cammino_Loyalty_Block_Adminhtml_Point_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('point_form', array('legend' => 'Informações do Ponto'));

		$fieldset->addField('customer_id', 'text', array(
			'label'    	=> 'ID Cliente',
			'name'      => 'customer_id',
			'index'     => 'customer_id',
			'required'  => true
		));
		
		$fieldset->addField('order_id', 'text', array(
			'label'    	=> 'ID Pedido',
			'name'      => 'order_id',
			'index'     => 'order_id',
			'required'  => true,
            'renderer' => $this->_getOrderIncrementIdRenderer()
		));
		
		$fieldset->addField('direction', 'select', array(
			'label'    	=> 'Tipo',
			'name'      => 'direction',
			'index'     => 'direction',
			'required'  => true,
			'values' 	=> array(
				array('value' => '', 'label' => ''),
				array('value' => 'credit', 'label' => 'Crédito'),
				array('value' => 'debit', 'label' => 'Débito')
			)
		));
		
		$fieldset->addField('amount', 'text', array(
			'label'    	=> 'Valor',
			'name'      => 'amount',
			'index'     => 'amount',
			'required'  => true
		));
		
		$fieldset->addField('points', 'text', array(
			'label'    	=> 'Pontos',
			'name'      => 'points',
			'index'     => 'points',
			'required'  => true
		));
		
		$fieldset->addField('money_to_point', 'text', array(
			'label'    	=> 'Real para Ponto',
			'name'      => 'money_to_point',
			'index'     => 'money_to_point',
			'required'  => true
		));
		
		$fieldset->addField('point_to_money', 'text', array(
			'label'    	=> 'Ponto para Real',
			'name'      => 'point_to_money',
			'index'     => 'point_to_money',
			'required'  => true
		));
		
		$fieldset->addField('status', 'select', array(
			'label'    	=> 'Status',
			'name'      => 'status',
			'index'     => 'status',
			'required'  => true,
			'values' 	=> array(
				array('value' => '', 'label' => ''),
				array('value' => 'approved', 'label' => 'Aprovado'),
				array('value' => 'pending', 'label' => 'Pendente'),
				array('value' => 'canceled', 'label' => 'Cancelado'),
			)
		));

		if (Mage::getSingleton('adminhtml/session')->getPointData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getPointData());
			Mage::getSingleton('adminhtml/session')->setPointData(null);
		} else if ( Mage::registry('point_data') ) {
			$form->setValues(Mage::registry('point_data')->getData());
		}
		return parent::_prepareForm();
	}
    protected function _getOrderIncrementIdRenderer()
    {
        return $this->getLayout()->createBlock(
            'loyalty/adminhtml_renderer_orderIncrementId',
            '',
            array('is_render_to_js_template' => true)
        );
    }
	
}