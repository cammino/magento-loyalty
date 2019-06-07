<?php
class Cammino_Loyalty_Block_Form_Pay extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('loyalty/form/pay.phtml');
    }
}