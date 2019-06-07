<?php

class Cammino_Loyalty_GeneralController extends Mage_Core_Controller_Front_Action {

    public function testeAction() {
        $x = Mage::getModel("loyalty/points")->getAvailablePoints();
        var_dump($x);
        die;
        // $helper = Mage::helper("loyalty");
        // $model = Mage::getModel("loyalty/loyalty");
        // $data = array(
        //     "customer_id" => 1,
        //     "order_id" => 2,
        //     "direction" => 'debit',
        //     "amount" => 12.4,
        //     "points" => 1000,
        //     "money_to_point" => 1000,
        //     "point_to_money" => 10,
        //     "status" => 'pending',
        //     "created_at" => '2019-02-27 13:02:13',
        //     "updated_at" => '2019-02-27 13:02:13',
        // );
        // $model->setData($data)->save();
        // die;
    }
}