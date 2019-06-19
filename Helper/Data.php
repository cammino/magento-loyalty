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
    public function getPointToMoney()
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
    * Function responsible for convert money to points
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
    * Function responsible for convert points to money
    *
    * @param int $points User points
    *
    * @return float
    */
    public function calcDiscount($points)
    {
        $pointToMoney = $this->getPointToMoney();
        return (float) floor($points / $pointToMoney);
    }


    /**
    * Function responsible for convert discount used in order to points
    *
    * @param float $discount Order discount by points
    *
    * @return int
    */
    public function revertDiscountInPoints($discount)
    {
        $pointToMoney = $this->getPointToMoney();
        return (int) floor($discount * $pointToMoney);
    }

    /**
    * Function responsible get date/time in mysql format
    *
    * @param string|null $date Date string
    *
    * @return string
    */
    public function getTimestamp($date = false)
    {
        if(!$date) {
            return date('Y-m-d H:i:s', strtotime('now'));
        } else {
            return date('Y-m-d H:i:s', strtotime($date));
        }
    }

    /**
    * Function responsible for set loyalty discount applied
    *
    * @return null
    */
    public function setLoyaltyDiscount($value)
    {
		Mage::getSingleton('core/session')->setLoyaltyDiscount(floatval($value));
	}

    /**
    * Function responsible for get loyalty discount applied
    *
    * @return float
    */
    public function getLoyaltyDiscount()
    {
		return (float) Mage::getSingleton('core/session')->getLoyaltyDiscount();
    }
    
    /**
    * Function responsible reset Loyalty discount session
    *
    * @return null
    */
    public function resetLoyaltyDiscount()
    {
        Mage::getSingleton('core/session')->unsLoyaltyDiscount();
    }

    /**
    * Function responsible check if das loyalty discount applied
    *
    * @return bool
    */
    public function hasLoyaltyDiscountApplied()
    {
		return (float) $this->getLoyaltyDiscount() > 0;
    }

    /**
    * Function responsible write in log file
    *
    * @param string $message Message that will be logged
    *
    * @return null
    */
    public function log($message)
    {
        Mage::log($message, null, "loyalty.log");
    }
}