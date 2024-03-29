<?php
/**
* Points.php
*
* @category Cammino
* @package  Cammino_Loyalty
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-loyalty
*/

class Cammino_Loyalty_Model_Points extends Mage_Core_Model_Abstract
{
    /**
    * Function responsible return sum all user points
    *
    * @return int
    */
    public function getAvailablePoints()
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            
            $collectionCredit = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToFilter('customer_id', $customerId);

            $collectionCredit->getSelect()->where("(direction = 'credit' AND status = 'approved' AND DATE(expires_at) > DATE(NOW()))");

            $total = 0;

            $i = 0;
            $firstValidCreditId = 0;
            foreach($collectionCredit as $item) {
                if ($i == 0) {
                    $firstValidCreditId = $item->getId();
                }
                $total += $item->getPoints();
                $i++;
            }

            $collectionDebit = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToFilter('customer_id', $customerId);
            $collectionDebit->getSelect()->where("(direction = 'debit' AND status != 'canceled' AND id > ".$firstValidCreditId.")");
            
            foreach($collectionDebit as $item) {
                $total += $item->getPoints();
            }
            
            return $total;
        }
    }

    /**
    * Function responsible for return sum of all user approved points
    *
    * @param int $orderId Order Id
    *
    * @return int
    */
    public function generatePoints($orderId)
    {
        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            $code = $order->getPayment()->getMethodInstance()->getCode();

            // Only generate points if is not loyalty payment method
            if($code != "loyalty") {
                $helper = Mage::helper("loyalty");
                $loyalty = Mage::getModel("loyalty/loyalty");
                $total = $order->getGrandTotal();
                $shippingDiscount = Mage::helper("loyalty")->getShippingDiscount();
                $points = $helper->calcPoints($total);

                if ($shippingDiscount) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $shippingAmount = $quote->getShippingAddress()->getShippingAmount();

                    $total = $order->getGrandTotal() - $shippingAmount;
                }

                if ($total > 0) {
                    $points = $helper->calcPoints($total);
                    $data = array(
                        "customer_id"       => $order->getCustomerId(),
                        "order_id"          => $order->getId(),
                        "direction"         => 'credit',
                        "amount"            => $total,
                        "points"            => $points,
                        "money_to_point"    => $helper->getMoneyToPoint(),
                        "point_to_money"    => $helper->getPointToMoney(),
                        "status"            => 'pending',
                        "created_at"        => $helper->getTimestamp(),
                        "updated_at"        => $helper->getTimestamp(),
                        "expires_at"        => date('Y-m-d H:i:s', strtotime($helper->getTimestamp() . ' + ' . $helper->getDaysToExpire() . ' days')),
                    );

                    $saved = $loyalty->setData($data)->save();
                    if(!$saved) {
                        $helper->log("Erro ao salvar os pontos no banco após concluir o pedido: " . $order->getId());
                    }
                }
            }
        } catch (Exception $e) {
            $helper->log($e->getMessage());
        }
    }


    /**
    * Function responsible for return sum of all user approved points
    *
    * @param int $orderId Order Id
    *
    * @return int
    */
    public function debitPoints($orderId)
    {
        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            $code = $order->getPayment()->getMethodInstance()->getCode();
            $helper = Mage::helper("loyalty");
            
            if($code == "loyalty" || $helper->hasLoyaltyDiscountApplied()) {
                $loyalty = Mage::getModel("loyalty/loyalty");
                $total = $order->getGrandTotal();

                if($code == "loyalty") {
                    $points = $helper->revertDiscountInPoints($total);
                    $status = "approved";
                } else {
                    $points = $this->getAvailablePoints();
                    $status = "pending";
                }


                $data = array(
                    "customer_id"       => $order->getCustomerId(),
                    "order_id"          => $order->getId(),
                    "direction"         => 'debit',
                    "amount"            => $total,
                    "points"            => $points * -1,
                    "money_to_point"    => $helper->getMoneyToPoint(),
                    "point_to_money"    => $helper->getPointToMoney(),
                    "status"            => $status,
                    "created_at"        => $helper->getTimestamp(),
                    "updated_at"        => $helper->getTimestamp(),
                );

                $saved = $loyalty->setData($data)->save();
                $helper->resetLoyaltyDiscount();

                if(!$saved) {
                    $helper->log("Erro ao salvar os pontos no banco após concluir o pedido: " . $order->getId());
                }
            }
        } catch (Exception $e) {
            $helper->log($e->getMessage());
        }
    }

    /**
    * Function responsible for return all points registered
    *
    * @param int $customerId User Id
    *
    * @return object
    */
    public function getAllPoints() {
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            return Mage::getModel("loyalty/loyalty")->getCollection()->addFieldToFilter('customer_id', $customerId);
        } return false;
    }
}