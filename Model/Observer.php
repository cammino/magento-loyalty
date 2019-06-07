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
        
        if($helper->isActive()) {
            $orderId = $observer->getOrder()->getId();
            $model->generatePoints($orderId);
            $model->debitPoints($orderId);
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
            $order = $observer->getOrder();
            
            $loyalty = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFirstItem();

            if($loyalty && $loyalty->getId()) {
                $loyalty->setStatus("approved");
                $loyalty->setUpdatedAt($helper->getTimestamp());
                $saved = $loyalty->save();

                if(!$saved) {
                    $helper->log("Erro ao atualizar o status do pedido: " . $order->getId() . "para approved");
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
            
            $loyalty = Mage::getModel("loyalty/loyalty")->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFirstItem();

            if($loyalty && $loyalty->getId()) {
                $loyalty->setStatus("canceled");
                $loyalty->setUpdatedAt($helper->getTimestamp());
                $saved = $loyalty->save();

                if(!$saved) {
                    $helper->log("Erro ao atualizar o status do pedido: " . $order->getId() . "para canceled");
                }
            }
        } catch(Exception $e) {
            $helper->log($e->getMessage());
        }

    }
}