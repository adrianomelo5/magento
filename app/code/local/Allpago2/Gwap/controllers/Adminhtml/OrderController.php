<?php

class Allpago_Gwap_Adminhtml_OrderController extends Mage_Adminhtml_Controller_action
{

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
    
    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }    

    public function removecreditAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        
        $session = $this->_getSession();
        if (!$order = $this->_initOrder()) {
            return;
        }   
        
        $payment = $order->getPayment();
        if($payment->getMethod()=='gwap_cc' || $payment->getMethod()=='gwap_oneclick' || $payment->getMethod()=='gwap_2cc'){

            $log = Mage::getModel('allpago_mc/log');
            try {
                $payment->getMethodInstance()->removeCredit($order);    
                $log->add($order->getId(), 'Payment', 'removecredit()', 'reversed', 'Pagamento cancelado' );
                $this->_getSession()->addSuccess($this->__('O pagamento foi estornado com sucesso.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $log->add($order->getId(), 'Payment', 'removecredit()', 'error', 'Pagamento não cancelado',$e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Falha ao estornar pagamento. Verifique os logs de erro.'));
                Mage::logException($e);
                $log->add($order->getId(), 'Payment', 'removecredit()', 'error', 'Pagamento não cancelado',$e->getMessage());
            }
            
        } else {
            $session->addError(Mage::helper("gwap")->__("Este tipo de pagamento não pode ser estornado"));
        }
        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));

    }
}