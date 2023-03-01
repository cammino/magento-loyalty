<?php

class Cammino_Loyalty_Model_Job
{
    public function notify() {

        Mage::log('entrou no job', null, 'job_loyalty.log');


        
        $creditCollection = Mage::getModel("loyalty/loyalty")->getCollection();
        $creditCollection->getSelect()->where("(direction = 'credit' AND status = 'approved' AND expires_at BETWEEN(TODAY(), TODAY() + 2))");
        
        Mage::log('filtrou:', null, 'job_loyalty.log');

        Mage::log($creditCollection, null, 'job_loyalty.log');

    }

}