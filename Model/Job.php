<?php

class Cammino_Loyalty_Model_Job
{
    public function notify() {
        $helper = Mage::helper("loyalty");
        $creditCollection = Mage::getModel("loyalty/loyalty")->getCollection();
        $creditCollection->getSelect()->where("(direction = 'credit' AND status = 'approved' AND DATE(expires_at) > DATE(NOW()))");
        if (!empty(Mage::getStoreConfig('loyalty/advanced/notice_one'))) {
            $noticeDay1 = date('Y-m-d', strtotime($helper->getTimestamp() . ' + ' . Mage::getStoreConfig('loyalty/advanced/notice_one') . ' days'));
        }
        if (!empty(Mage::getStoreConfig('loyalty/advanced/notice_two'))) {
            $noticeDay2 = date('Y-m-d', strtotime($helper->getTimestamp() . ' + ' . Mage::getStoreConfig('loyalty/advanced/notice_two') . ' days'));
        }
        if (!empty(Mage::getStoreConfig('loyalty/advanced/notice_three'))) {
            $noticeDay3 = date('Y-m-d', strtotime($helper->getTimestamp() . ' + ' . Mage::getStoreConfig('loyalty/advanced/notice_three') . ' days'));
        }
        foreach($creditCollection as $item) {
            $expirationDay = date('Y-m-d', strtotime($item->getExpiresAt()));
            $sendmail = false;
            if ((!empty($noticeDay1)) && ($expirationDay == $noticeDay1)) {
                $sendmail = true;
            }
            if ((!empty($noticeDay2)) && ($expirationDay == $noticeDay2)) {
                $sendmail = true;
            }
            if ((!empty($noticeDay3)) && ($expirationDay == $noticeDay3)) {
                $sendmail = true;
            }
            if ($sendmail) {
                $this->sendEmail($item);
            }
        }
    }

    private function sendEmail($credit) {
        $customer = Mage::getModel('customer/customer')->load($credit->getCustomerId());
        $mailer = Mage::getModel("core/email_template_mailer");
        $emailInfo = Mage::getModel("core/email_info");
        $emailInfo->addTo($customer->getEmail(), $customer->getFirstname());
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender(Mage::getStoreConfig( Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $customer->getStoreId()));
        $mailer->setStoreId($customer->getStoreId());
        $mailer->setTemplateId("loyalty_email");
        $mailer->setTemplateParams(array(
            'subject'         => 'Seus pontos estão expirando!',
            'points'          => $credit->getPoints(),
            'expiration_date' => date('d/m', strtotime($credit->getExpiresAt()))
        ));
        $mailer->send();
    }

}