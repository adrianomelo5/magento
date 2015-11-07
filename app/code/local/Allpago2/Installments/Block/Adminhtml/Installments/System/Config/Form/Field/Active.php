<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System congifuration shipping methods allow all countries selec
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>


  class Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Active extends Varien_Data_Form_Element_Select
  {

  public function getAfterElementHtml()
  {
  return parent::getAfterElementHtml();
  $javaScript = "
  <script type=\"text/javascript\">
  Event.observe('{$this->getHtmlId()}', 'change', function(){
  element=$('{$this->getHtmlId()}').value;

  });
  </script>";
  return $javaScript . parent::getAfterElementHtml();
  }

  }

  }
 */
class Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Active extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $html = parent::_getElementHtml($element);

        $html .= "<script>Event.observe('allpago_installments_active', 'change', function(){
                     enableInstallmentsAdvanced();

                });

                Event.observe(window, 'load', function(){
                   enableInstallmentsAdvanced();
                });

                function enableInstallmentsAdvanced(){
                    advanced_element=$('allpago_installments_active').value;
                    if( advanced_element != 'advance' ){
                        $('row_allpago_installments_parcelas').hide();
                    }else{
                        $('row_allpago_installments_parcelas').show();
                    }
                    
                    auto_element=$('allpago_installments_active').value;
                    if( auto_element != 'auto' ){
                        $('row_allpago_installments_n_max_parcelas').hide();
                    }else{
                        $('row_allpago_installments_n_max_parcelas').show();
                    }
                }</script>";
        return $html;
    }

}

