<?php

class Cammino_Loyalty_Block_Adminhtml_Point_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('point_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle('Pontos');
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => 'Campos',
          'title'     => 'Campos',
          'content'   => $this->getLayout()->createBlock('skinclub/adminhtml_point_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}