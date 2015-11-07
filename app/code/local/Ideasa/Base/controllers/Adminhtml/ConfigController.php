<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'System' . DS . 'ConfigController.php';

class Ideasa_Base_Adminhtml_ConfigController extends Mage_Adminhtml_System_ConfigController {

    public function editAction() {
        $this->_title($this->__('Configurações PagSeguro'));

        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        $configFields = Mage::getSingleton('adminhtml/config');

        $sections = $configFields->getSections($current);
        $section = $sections->$current;
        $hasChildren = $configFields->hasChildren($section, $website, $store);
        if (!$hasChildren && $current) {
            $this->_redirect('*/*/', array('website' => $website, 'store' => $store));
        }

        $this->loadLayout();

        $this->_setActiveMenu('ideasa/base');
        $this->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo(array($current));

        //$this->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'), $this->getUrl('*/system'));

        //$this->getLayout()->getBlock('left')->append($this->getLayout()->createBlock('adminhtml/system_config_tabs')->initTabs());

        if ($this->_isSectionAllowedFlag) {
            $this->_addContent($this->getLayout()->createBlock('adminhtml/system_config_edit')->initForm());

            //$this->_addJs($this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/shipping/ups.phtml'));
            $this->_addJs($this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/config/js.phtml'));
            //$this->_addJs($this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/shipping/applicable_country.phtml'));

            $this->renderLayout();
        }
    }
}