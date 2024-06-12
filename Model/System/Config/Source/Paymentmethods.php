<?php
class Cammino_Loyalty_Model_System_Config_Source_Paymentmethods
{
    public function toOptionArray()
    {
        $methods = array();
        $payments = Mage::getSingleton('payment/config')->getAllActiveMethods();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[] = array(
                'value' => $paymentCode,
                'label' => $paymentTitle
            );
        }
        
        return $methods;
    }
}