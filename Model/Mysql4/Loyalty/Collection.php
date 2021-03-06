<?php
/**
* Collection.php
*
* @category Cammino
* @package  Cammino_Loyalty
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-loyalty
*/

class Cammino_Loyalty_Model_Mysql4_Loyalty_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
    * Function responsible for construct
    *
    * @return object
    */
    public function _construct()
    {
        parent::_construct();
        $this->_init('loyalty/loyalty');
    }
}