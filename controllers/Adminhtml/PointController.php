<?php
class Cammino_Loyalty_Adminhtml_PointController extends Mage_Adminhtml_Controller_action
{
    
    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();    
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('loyalty/loyalty')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('point_data', $model);

            $this->loadLayout();
        
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            
            $this->_addContent($this->getLayout()->createBlock('loyalty/adminhtml_point_edit'))
                ->_addLeft($this->getLayout()->createBlock('loyalty/adminhtml_point_edit_tabs'));

            $this->renderLayout();
            
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Item não encontrado');
            $this->_redirect('*/*/');
        }
    }
 
    public function newAction()
    {
        $this->_forward('edit');
    }
 
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('loyalty/loyalty');
            $data = $this->_filterDates($data, array('purchase_date'));

            $id = $this->getRequest()->getParam('id');
            $model->setData($data)->setId($id);
            $model->setUpdatedAt(Mage::helper("loyalty")->getTimestamp());
            
            if ($id == NULL) {
                $model->setCreatedAt(Mage::helper("loyalty")->getTimestamp());
                $model->setExpiresAt(date('Y-m-d H:i:s', strtotime(Mage::helper("loyalty")->getTimestamp() . ' + ' . Mage::helper("loyalty")->getDaysToExpire() . ' days')));
            }
            
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess('Informações do ponto salvas com sucesso');
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
               
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError('Não foi possível salvar o ponto');
        $this->_redirect('*/*/');
    }
 
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('loyalty/loyalty');
                $model->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess('Ponto excluido com sucesso');
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
}