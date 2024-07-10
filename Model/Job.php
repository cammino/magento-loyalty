<?php

class Cammino_Loyalty_Model_Job
{
    public function notify() {
        try {
            $helper = Mage::helper("loyalty");

            $customerCollection = Mage::getModel('customer/customer')->getCollection();

            foreach($customerCollection as $customer) {
                $collectionDebit = Mage::getModel("loyalty/loyalty")->getCollection()
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->setOrder('created_at', 'DESC');
                $collectionDebit->getSelect()->where("(direction = 'debit' AND status != 'canceled')");
                $lastDebit = $collectionDebit->getFirstItem();
                $creditCollection = Mage::getModel("loyalty/loyalty")->getCollection()
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->addFieldToFilter('created_at', array('from' => $lastDebit->getCreatedAt()));
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
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'job_loyalty.log');
        }
    }

    private function sendEmail($credit) {
        try{
            $customer = Mage::getModel('customer/customer')->load($credit->getCustomerId());
            $templateVars = array(
                'points' => $credit->getPoints(),
                'expiration_date' => date('d/m', strtotime($credit->getExpiresAt())),
                'amount' => number_format($credit->getAmount(), 2, ',', '.')
            );
            if (!empty(Mage::getStoreConfig('loyalty/advanced/show_points'))) {
                $emailTemplate = Mage::getModel('core/email_template')->loadDefault('loyalty_email');  
                $emailTemplate->setTemplateSubject('Seus pontos estão expirando!');
            } else {
                $emailTemplate = Mage::getModel('core/email_template')->loadDefault('loyalty_email_nopoints');
                $emailTemplate->setTemplateSubject('Seus créditos estão expirando!');
            }
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email', $customer->getStoreId()));
            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', $customer->getStoreId()));
            $emailTemplate->setType('html');
            $emailTemplate->setTemplateParams($templateVars);
            $emailTemplate->send($customer->getEmail(), $customer->getFirstname(), $templateVars);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'job_loyalty.log');
        }
    }

}