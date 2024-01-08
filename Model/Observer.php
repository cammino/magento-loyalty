<?php
/**
 * Observer.php
 * 
 * @category Cammino
 * @package  Cammino_Loyalty
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-loyalty
 */

class Cammino_Loyalty_Model_Observer
{

    public function checkoutMinLoyaltyCheck() {
        $helper = Mage::helper("loyalty");
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $subtotal = $quote->getSubtotal();
        if ($subtotal < $helper->getMinValToUsePoints()) {
            $helper->setLoyaltyDiscount(0);
        }
    }
    public function checkPaymentmethod($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        if ($method->getCode() == 'loyalty') {
            if (!Mage::getStoreConfig('loyalty/general/active')) {
                $result = $observer->getEvent()->getResult();
                $result->isAvailable = false;
            }
        }
    }

    /**
     * Function responsible generate points for the customer
     * after order has been created
     * 
     * @return null
     */
    public function orderCreated($observer)
    {
        $helper = Mage::helper("loyalty");
        $model = Mage::getModel("loyalty/points");
        
        $minValToGeneratePoints = $helper->getMinValToGeneratePoints();
        $minValToUsePoints = $helper->getMinValToUsePoints();
        
        if($helper->isActive()) {
            $orderId = $observer->getOrder()->getId();
            $order = Mage::getModel('sales/order')->load($orderId);
            $grandTotal = $order->getGrandTotal();
            
            if($grandTotal >= $minValToUsePoints) {
                $model->debitPoints($orderId);
            }

            if($grandTotal >= $minValToGeneratePoints) {
                $model->generatePoints($orderId);
            }
        }
    }

    /**
     * Function responsible for get the order when its approved 
     * and changing its pending status to approved
     * 
     * @return null
     */
    public function invoiceCreated($observer)
    {
        try {
            $helper = Mage::helper("loyalty");
            $order = $observer->getDataObject()->getOrder();
            
            $collection = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $order->getId());

            foreach($collection as $loyalty) {
                if($loyalty->getId()) {
                    $loyalty->setStatus("approved");
                    $loyalty->setUpdatedAt($helper->getTimestamp());
                    $loyalty->setData('expires_at', (date('Y-m-d H:i:s', strtotime(Mage::helper("loyalty")->getTimestamp() . ' + ' . Mage::helper("loyalty")->getDaysToExpire() . ' days'))));
                    $saved = $loyalty->save();
    
                    if(!$saved) {
                        $helper->log("Erro ao atualizar o status do pedido: " . $order->getId() . "para approved");
                    }
                }
            }
            
        } catch(Exception $e) {
            $helper->log($e->getMessage());
        }
    }

    /**
     * Function responsible for get the order when its canceled 
     * and changing its pending status to canceled
     * 
     * @return null
     */
    public function orderCanceled($observer) {        
        try {
            $helper = Mage::helper("loyalty");
            $order = $observer->getEvent()->getPayment()->getOrder();
            
            $collection = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $order->getId());
            
            foreach($collection as $loyalty) {
                if($loyalty && $loyalty->getId()) {
                    $loyalty->setStatus("canceled");
                    $loyalty->setUpdatedAt($helper->getTimestamp());
                    $saved = $loyalty->save();
    
                    if(!$saved) {
                        $helper->log("Erro ao atualizar os pontos do pedido: " . $order->getId() . "para canceled");
                    }
                }
            }

        } catch(Exception $e) {
            $helper->log($e->getMessage());
        }
    }

    /**
     * Function responsible to check if user is logout
     * and reset Loyalty Points
     *
     * @return null
     */
    public function customerLogout($observer)
    {
        $helper = Mage::helper("loyalty");
        $model = Mage::getModel("loyalty/points");

        if($helper->isActive()) {
            Mage::helper("loyalty")->resetLoyaltyDiscount();
        }
    }
}