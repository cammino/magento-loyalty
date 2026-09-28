<?php
/**
* Api.php
*
* @category Cammino
* @package  Cammino_Loyalty
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-loyalty
*/

class Cammino_Loyalty_Model_Points_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
    * Function responsible for credit points to a customer.
    * The amount unit follows the show_points config of the customer's store:
    * points when enabled, money (credits) otherwise
    *
    * @param int         $customerId Customer Id
    * @param float       $amount     Amount in points or money
    * @param string|null $expiresAt  Expiration date (Y-m-d or Y-m-d H:i:s)
    *
    * @return array
    */
    public function add($customerId, $amount, $expiresAt = null)
    {
        $helper = Mage::helper("loyalty");
        $customer = $this->_getCustomer($customerId);
        $storeId = $this->_getStoreId($customer);
        $points = $this->_convertAmountToPoints($amount, $storeId);

        if (empty($expiresAt)) {
            $expiresAt = date('Y-m-d H:i:s', strtotime($helper->getTimestamp() . ' + ' . $helper->getDaysToExpire($storeId) . ' days'));
        } else {
            if (strtotime($expiresAt) === false) {
                $this->_fault('invalid_date');
            }
            $expiresAt = $helper->getTimestamp($expiresAt);
            if (date('Y-m-d', strtotime($expiresAt)) < date('Y-m-d')) {
                $this->_fault('invalid_date', 'Expiration date must be today or a future date.');
            }
        }

        $data = array(
            "customer_id"       => $customer->getId(),
            "order_id"          => 0,
            "direction"         => 'credit',
            "amount"            => ($points / $helper->getPointToMoney($storeId)),
            "points"            => $points,
            "money_to_point"    => $helper->getMoneyToPoint($storeId),
            "point_to_money"    => $helper->getPointToMoney($storeId),
            "status"            => 'approved',
            "created_at"        => $helper->getTimestamp(),
            "updated_at"        => $helper->getTimestamp(),
            "expires_at"        => $expiresAt,
        );

        return $this->_save($data, $storeId);
    }

    /**
    * Function responsible for debit points from a customer.
    * The amount unit follows the show_points config of the customer's store:
    * points when enabled, money (credits) otherwise
    *
    * @param int   $customerId Customer Id
    * @param float $amount     Amount in points or money
    *
    * @return array
    */
    public function remove($customerId, $amount)
    {
        $helper = Mage::helper("loyalty");
        $model = Mage::getModel("loyalty/points");
        $customer = $this->_getCustomer($customerId);
        $storeId = $this->_getStoreId($customer);
        $points = $this->_convertAmountToPoints($amount, $storeId);

        if ($points > (int) $model->getAvailablePoints($customer->getId())) {
            $this->_fault('insufficient_balance');
        }

        $data = array(
            "customer_id"       => $customer->getId(),
            "order_id"          => 0,
            "direction"         => 'debit',
            "amount"            => (($points / $helper->getPointToMoney($storeId)) * -1),
            "points"            => $points * -1,
            "money_to_point"    => $helper->getMoneyToPoint($storeId),
            "point_to_money"    => $helper->getPointToMoney($storeId),
            "status"            => 'approved',
            "created_at"        => $helper->getTimestamp(),
            "updated_at"        => $helper->getTimestamp(),
            "credits_used"      => $model->getCreditsUsed($points, $customer->getId()),
        );

        return $this->_save($data, $storeId);
    }

    /**
    * Function responsible for return the customer's available balance
    *
    * @param int $customerId Customer Id
    *
    * @return array
    */
    public function balance($customerId)
    {
        $customer = $this->_getCustomer($customerId);
        $storeId = $this->_getStoreId($customer);

        return $this->_getBalance($customer->getId(), $storeId);
    }

    /**
    * Function responsible for save the points entry and return it with the new balance
    *
    * @param array $data    Points entry data
    * @param int   $storeId Store Id
    *
    * @return array
    */
    protected function _save($data, $storeId)
    {
        try {
            $loyalty = Mage::getModel("loyalty/loyalty")->setData($data)->save();
        } catch (Exception $e) {
            Mage::helper("loyalty")->log("Erro ao salvar pontos via API: " . $e->getMessage());
            $this->_fault('save_error', $e->getMessage());
        }

        $balance = $this->_getBalance($loyalty->getCustomerId(), $storeId);

        return array(
            'id'             => (int) $loyalty->getId(),
            'customer_id'    => (int) $loyalty->getCustomerId(),
            'direction'      => $loyalty->getDirection(),
            'points'         => (int) $loyalty->getPoints(),
            'amount'         => (float) $loyalty->getAmount(),
            'expires_at'     => $loyalty->getExpiresAt(),
            'balance_points' => $balance['points'],
            'balance_amount' => $balance['amount'],
        );
    }

    /**
    * Function responsible for return the customer's balance in points and money
    *
    * @param int $customerId Customer Id
    * @param int $storeId    Store Id
    *
    * @return array
    */
    protected function _getBalance($customerId, $storeId)
    {
        // same as the customer panel (Cammino_Loyalty_Block_Customer::getMyPoints)
        $points = max(0, (int) Mage::getModel("loyalty/points")->getAvailablePoints($customerId));

        return array(
            'customer_id' => (int) $customerId,
            'points'      => $points,
            'amount'      => $points / Mage::helper("loyalty")->getPointToMoney($storeId),
        );
    }

    /**
    * Function responsible for convert the amount to points.
    * When the store shows points (show_points config) the amount is already in points,
    * otherwise it is in money and is converted using the point_to_money config
    *
    * @param float $amount  Amount in points or money
    * @param int   $storeId Store Id
    *
    * @return int
    */
    protected function _convertAmountToPoints($amount, $storeId)
    {
        $amount = str_replace(',', '.', (string) $amount);

        if (!is_numeric($amount) || $amount <= 0) {
            $this->_fault('invalid_amount');
        }

        if (Mage::getStoreConfigFlag('loyalty/advanced/show_points', $storeId)) {
            if (floor($amount) != $amount) {
                $this->_fault('invalid_amount', 'Amount must be an integer number of points.');
            }

            return (int) $amount;
        }

        // round before floor to avoid float errors (e.g. 0.29 * 100 = 28.999...)
        $points = (int) floor(round($amount * Mage::helper("loyalty")->getPointToMoney($storeId), 4));

        if ($points <= 0) {
            $this->_fault('invalid_amount', 'Amount is too low to be converted to points.');
        }

        return $points;
    }

    /**
    * Function responsible for load the customer
    *
    * @param int $customerId Customer Id
    *
    * @return Mage_Customer_Model_Customer
    */
    protected function _getCustomer($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load((int) $customerId);

        if (!$customer->getId()) {
            $this->_fault('customer_not_exists');
        }

        return $customer;
    }

    /**
    * Function responsible for resolve the customer's store, used for the conversion configs
    *
    * @param Mage_Customer_Model_Customer $customer Customer
    *
    * @return int
    */
    protected function _getStoreId($customer)
    {
        $storeId = $customer->getStoreId();

        if (!$storeId) {
            $defaultStore = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore();
            $storeId = $defaultStore ? $defaultStore->getId() : Mage::app()->getDefaultStoreView()->getId();
        }

        if (!Mage::helper("loyalty")->isActive($storeId)) {
            $this->_fault('module_disabled');
        }

        if (Mage::helper("loyalty")->getPointToMoney($storeId) <= 0) {
            $this->_fault('invalid_config');
        }

        return (int) $storeId;
    }
}
