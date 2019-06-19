<?php
class Cammino_Loyalty_Block_Adminhtml_Point extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
  	$this->_controller = 'adminhtml_point';
    $this->_blockGroup = 'loyalty';
    $this->_headerText = 'Fidelidade';
	$this->_addButtonLabel = 'Adicionar Pontos';
    parent::__construct();
  }
}