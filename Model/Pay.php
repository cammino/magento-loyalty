<?php
class Cammino_Loyalty_Model_Pay extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'loyalty';
    protected $_formBlockType = 'loyalty/form_pay';

    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;

    /**
     * Check authorise availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->_canCapture;
    }
}
?>