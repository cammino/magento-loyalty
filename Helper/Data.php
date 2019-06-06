<?php
/**
* Data.php
*
* @category Cammino
* @package  Cammino_Loyalty
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-loyalty
*/

class Cammino_Loyalty_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
    * Function responsible for check if module is active
    *
    * @return boolean
    */
    public function isActive()
    {
        return (bool) Mage::getStoreConfig('loyalty/general/active');
    }

    /**
    * Function responsible for get points_to_money config
    *
    * @return float
    */
    public function getPointsToMoney()
    {
        return (float) Mage::getStoreConfig('loyalty/points/point_to_money');
    }
    
    /**
    * Function responsible for get points_to_money config
    *
    * @return float
    */
    public function getMoneyToPoint()
    {
        return (float) Mage::getStoreConfig('loyalty/points/money_to_point');
    }

    /**
    * Function responsible for convert order total in points
    *
    * @param float $total Order grand total
    *
    * @return int
    */
    public function calcPoints($total)
    {
        $moneyToPoint = $this->getMoneyToPoint();
        return (int) floor($total / $moneyToPoint);
    }

    /**
    * Function responsible get date/time in mysql format
    *
    * @param string|null $date Date string
    *
    * @return string
    */
    public function getTimestamp($date = false) {
        if(!$date) {
            return date('Y-m-d H:i:s', strtotime('now'));
        } else {
            return date('Y-m-d H:i:s', strtotime($date));
        }
    }

    /**
    * Function responsible write in log file
    *
    * @param string $message Message that will be logged
    *
    * @return null
    */
    public function log($message) {
        Mage::log($message, null, "loyalty.log");
    }
}
