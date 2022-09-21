<?php
class Cammino_Loyalty_Block_Customer extends Mage_Core_Block_Template
{
    public function getMyPoints() {
        $points = Mage::getModel("loyalty/points")->getAvailablePoints();
        return $points < 0 ? 0 : $points;
    }
    
    public function getMyHistory() {
        $history = array();
        $collection = Mage::getModel("loyalty/points")->getAllPoints();
        
        foreach($collection as $point) {

            $type = $point->getDirection() == "debit" ? "Débito" : "Crédito";
            $points = $point->getPoints() < 0 ? $point->getPoints() . "pts" : "+".$point->getPoints() . "pts";
            
            if($point->getStatus() == "approved") {
                $status = "Aprovado";
            } else if($point->getStatus() == "pending") {
                $status = "Pendente";
            } else if($point->getStatus() == "canceled") {
                $status = "Cancelado";
            } else {
                $status = "--";
            }
            if(($status != "Cancelado") && (date('Y-m-d H:i:s') > $point->getData('expires_at'))) {
                $status = "Expirado";
            }

            $history[] = array (
                "class" => $point->getDirection() . " " . $point->getStatus(),
                "type" => $type,
                "order" => "#".$point->getOrderId(),
                "total" => Mage::helper('core')->currency($point->getAmount(), true, false),
                "points" => $points,
                "status" => $status,
                "date" => date('d/m/Y', strtotime($point->getCreatedAt()))
            );
        }

        return $history;
    }

}