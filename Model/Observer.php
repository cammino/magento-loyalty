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
            $subTotal = $order->getSubtotal();
            if($subTotal >= $minValToUsePoints) {
                $model->debitPoints($orderId);
            }

            if($grandTotal >= $minValToGeneratePoints) {
                $allowedMethodsArray = explode(',', Mage::getStoreConfig('loyalty/general/payment_methods_allowed_generate'));
                if(!in_array($order->getPayment()->getMethod(), $allowedMethodsArray)) {
                    return;
                }
                $model->generatePoints($orderId);
            }

            if ($order->getPayment()->getMethod() == 'loyalty' && $order->canInvoice()) {
                $invoice = $order->prepareInvoice();
                $invoice->register();
                $invoice->capture();

                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($order)
                    ->save();

                $order->addStatusHistoryComment('Invoice gerada automaticamente pelo método de pagamento.');
                $order->save();
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
    

    public function orderRefunded(Varien_Event_Observer $observer)
    {    
        try {
            $helper = Mage::helper("loyalty");
            $creditMemo = $observer->getEvent()->getCreditmemo();
            $order = $creditMemo->getOrder();
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

    public function alterOrderNumberToOrderId($observer) {
        $point = $observer->getEvent()->getObject();
        if (get_class($point) !== "Cammino_Loyalty_Model_Loyalty") {
            return;
        }
        $orderNumber = $point->getOrderId();
        if(empty(Mage::getModel('sales/order')->load($orderNumber)->getId())) {
            if (!empty(Mage::getModel('sales/order')->loadByIncrementId($orderNumber)->getId())) {
                $point->setOrderId(Mage::getModel('sales/order')->loadByIncrementId($orderNumber)->getId());
            }
        }
    }

}