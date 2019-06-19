<?php
// formulario para adicionar tablerate
class Cammino_Loyalty_Block_Adminhtml_Point_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('point_form', array('legend'=> 'Informações do Ponto'));

		$fieldset->addField('doctor_id', 'select', array(
			'label'    => 'Médico',
			'name'     => 'doctor_id',
			'required' => true,
			'values'   => Mage::getModel('skinclub/points')->getDoctors()
		));

		$fieldset->addField('patient_id', 'select', array(
			'label'    => 'Paciente',
			'name'     => 'patient_id',
			'values'   => Mage::getModel('skinclub/points')->getPatients()
		));
	  
	  $fieldset->addField('crm', 'text', array(
			'label'    => 'CRM',
			'name'     =>'crm',
			'index'     => 'crm',
		));

	  $fieldset->addField('value', 'text', array(
			'label'    => 'Valor do(s) Produto(s)',
			'name'     =>'value',
			'index'     => 'value',
			'required' => true,
			'class'     => 'validate-number',
		));

	  $fieldset->addField('bonus_value', 'text', array(
			'label'    => 'Pontos Recebidos',
			'name'     =>'bonus_value',
			'index'     => 'bonus_value',
			'required' => true,
			'class'     => 'validate-number',
		));

	  $fieldset->addField('type', 'select', array(
			'label'    => 'Tipo',
			'name'     => 'type',
			'required' => true,
			'values'   => array (
				array (
					'value' => 'medical_approved',
					'label' => 'Médico Aprovado'
				),
				array (
					'value' => 'medical_clinical_credit',
					'label' => 'Compra pelo Médico'
				),
				array (
					'value' => 'patient_restriction_credit',
					'label' => 'Compra pelo Paciente com Restrição'
				),
				array (
					'value' => 'patient_credit',
					'label' => 'Compra pelo Paciente sem Restrição'
				),
				array (
					'value' => 'debit',
					'label' => 'Débito'
				),
				array (
					'value' => 'debit_pending',
					'label' => 'Débito Pendente'
				)
			)
		));

	  $fieldset->addField('description', 'text', array(
			'label'    => 'Descrição',
			'name'     =>'description',
			'index'     => 'description',
		));
		$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

	  $fieldset->addField('data', 'date', array(
			'label'    => 'Data da Compra',
			'name'     => 'data',
			'index'    => 'data',
			'format'   => 'dd-MM-yyyy',
			'image'    => $this->getSkinUrl('images/grid-cal.gif'),

		));
		
	  $fieldset->addField('order_id', 'text', array(
			'label'    => 'Código do Pedido',
			'name'     =>'order_id',
			'index'     => 'order_id',
		));


		if (Mage::getSingleton('adminhtml/session')->getPointData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getPointData());
			Mage::getSingleton('adminhtml/session')->setPointData(null);
		} elseif ( Mage::registry('point_data') ) {
			$form->setValues(Mage::registry('point_data')->getData());
		}
		return parent::_prepareForm();
	}
}