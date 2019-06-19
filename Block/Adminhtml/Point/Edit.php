<?php

class Cammino_Loyalty_Block_Adminhtml_Point_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_point';
        $this->_blockGroup = 'skinclub';
        
        $this->_updateButton('save', 'label', 'Salvar');
        $this->_updateButton('delete', 'label', 'Excluir');
		
        $this->_addButton('saveandcontinue', array(
            'label'     => 'Salvar e Continuar Editando',
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('point_data') && Mage::registry('point_data')->getId() ) {
            return 'Editar';
        } else {
            return 'Adicionar';
        }
    }

    
}