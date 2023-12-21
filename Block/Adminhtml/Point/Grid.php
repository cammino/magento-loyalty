<?php

class Cammino_Loyalty_Block_Adminhtml_Point_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('cammino_loyalty');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$firstnameAttrId = Mage::helper("loyalty")->getFirstnameAttrId();
		$lastnameAttrId = Mage::helper("loyalty")->getLastnameAttrId();

		$collection = Mage::getModel('loyalty/loyalty')->getCollection();

		$collection->addFieldToFilter('customer1.attribute_id', array('eq' => array($firstnameAttrId)));
		$collection->addFieldToFilter('customer2.attribute_id', array('eq' => array($lastnameAttrId)));

		$collection->getSelect()->join( array('customer1' => customer_entity_varchar), 'customer1.entity_id = main_table.customer_id', array('customer_firstname' => 'customer1.value'));
		$collection->getSelect()->join( array('customer2' => customer_entity_varchar), 'customer2.entity_id = main_table.customer_id', array('customer_lastname' => 'customer2.value'));
		$collection->getSelect()->join( array('orders' => 'sales_flat_order'), 'orders.entity_id = main_table.order_id', array('increment_id' => 'orders.increment_id'));

		$collection->addExpressionFieldToSelect('customer_fullname', ' CONCAT(CONCAT(customer1.value, \' \'), customer2.value)  ', array());

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header'    => 'ID',
			'align'     => 'right',
			'width'     => '50px',
			'index'     => 'id',
		));

		$this->addColumn('customer_id', array(
			'header'    => 'Cliente ID',
			'align'     => 'right',
			'width'     => '50px',
			'index'     => 'customer_id',
			'filter_index'=>'main_table.customer_id',
		));

		$this->addColumn('customer', array(
			'header'    => 'Cliente',
			'align'     => 'right',
			'index'     => 'customer_fullname',
			'filter_condition_callback' => array($this, '_filterFullName')
		));

		$this->addColumn('increment_id', array(
			'header'    => 'Pedido',
			'align'     => 'right',
			'width'     => '100px',
			'index'     => 'increment_id'
		));
		
		$this->addColumn('direction', array(
			'header'    => 'Tipo',
			'align'     => 'right',
			'index'     => 'direction',
			'type'      => 'options',
			'options'   => array(
				'credit' 	=> 'Crédito',
				'debit'  	=> 'Débito'
			)
		));
		
		$this->addColumn('amount', array(
			'header'    => 'Valor',
			'align'     => 'right',
			'index'     => 'amount',
			'type'		=> 'price',
			'width'     => '250px',
			'currency_code' => 'BRL'
		));
		
		$this->addColumn('points', array(
			'header'    => 'Pontos',
			'align'     => 'right',
			'index'     => 'points'
		));
		
		$this->addColumn('created_at', array(
			'header'    => 'Criado em',
			'align'     => 'right',
			'index'     => 'created_at',
			'type' 		=> 'datetime',
			'renderer'  => 'Cammino_Loyalty_Block_Adminhtml_Point_Grid_Renderer_Createdat',
			'filter_index'=>'main_table.created_at',
		));
		
		$this->addColumn('updated_at', array(
			'header'    => 'Atualizado em',
			'align'     => 'right',
			'index'     => 'updated_at',
			'type' 		=> 'datetime',
			'renderer'  => 'Cammino_Loyalty_Block_Adminhtml_Point_Grid_Renderer_Updatedat'
		));
		
		$this->addColumn('expires_at', array(
			'header'    => 'Valido até',
			'align'     => 'right',
			'index'     => 'expires_at',
			'type' 		=> 'datetime',
			'renderer'  => 'Cammino_Loyalty_Block_Adminhtml_Point_Grid_Renderer_Expiresat'
		));

        $this->addColumn('status', array(
            'header'       => 'Status',
            'index'        => 'status',
            'filter_index' => 'main_table.status',
            'type'         => 'options',
            'options'      => array (
                'approved' => 'Aprovado',
                'pending'  => 'Pendente',
                'canceled' => 'Cancelado'
            )
        ));

	    $this->addColumn('action',
			array(
				'header'    =>  'Ação',
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => 'Editar',
						'url'       => array('base'=> '*/*/edit'),
						'field'     => 'id'
					)
				),
			'filter'    => false,
			'sortable'  => false,
			'index'     => 'stores',
			'is_system' => true,
		));
		return parent::_prepareColumns();
	}

	protected function _filterFullName($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
	        return $this;
		}
		
	    if (!empty($value)) {
			$this->getCollection()->getSelect()->where("customer1.value LIKE '%" . $value . "%' OR customer2.value LIKE '%" . $value . "%'");
	    }

	    return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}