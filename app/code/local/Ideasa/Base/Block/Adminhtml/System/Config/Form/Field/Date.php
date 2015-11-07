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

class Ideasa_Base_Block_Adminhtml_System_Config_Form_Field_Date extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        //$element->setFormat(Varien_Date::DATE_INTERNAL_FORMAT); //or other format
        $element->setFormat('dd/MM/yyyy'); //or other format
        $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
        return parent::render($element);
    }

}

?>
