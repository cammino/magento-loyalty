<?php

class Cammino_Loyalty_PaymentController extends Mage_Core_Controller_Front_Action {

    public function applyAction() {
        $discount = floatval($_POST["discount"]);
        $action = $_POST["action"];
        
        if($discount > 0 && $action != null) {
            if($action == "disable") {
                Mage::helper("loyalty")->resetLoyaltyDiscount();
                $this->respond(true, "discount desabled successfully");
            } else {

                // Verifica se o cliente tem esse valor disponivel para utilizar
                $availablePoints = Mage::getModel("loyalty/points")->getAvailablePoints();
                $availableDiscount = Mage::helper("loyalty")->calcDiscount($availablePoints);

                if($discount <= $availableDiscount) {
                    Mage::helper("loyalty")->setLoyaltyDiscount($discount);
                    $this->respond(true, "discount applied successfully");
                } else {
                    $this->respond(false, "trying to apply bigger discount than available");
                }
            }
        }
    }

    public function respond($status, $message) {
		$data = array(
			'status' => $status,
			'message' => $message
		);
		echo json_encode($data);
	}
}