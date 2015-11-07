<?php

/**
* 
* SuperEmpreendedor para Magento
* 
* @category     SuperEmpreendedor
* @packages     Base
* @copyright    Copyright (c) 2014 SuperEmpreendedor (http://www.superempreendedor.com/pagseguro)
* @version      1.16.2
* @license      http://www.superempreendedor.com/magento/licenca (Este arquivo Ã© propriedade do SuperEmpreendedor e nÃ£o pode ser copiado ou distribuÃ­do sem autorizaÃ§Ã£o.)
*
*/

class Ideasa_Base_Block_Adminhtml_System_Config_Form_Field_NeedHelp extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $imgHelp = $this->getSkinUrl('base/images/help.png');
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h6 style="background-color: #FFF8E9;padding: 5px" id="%s"><img src=' . $imgHelp . '></img>%s</h6></td></tr>', $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}