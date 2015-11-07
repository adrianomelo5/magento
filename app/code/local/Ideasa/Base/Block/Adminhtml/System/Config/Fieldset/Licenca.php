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

class Ideasa_Base_Block_Adminhtml_System_Config_Fieldset_Licenca extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $stringExp = '';
        if (extension_loaded('ionCube Loader')) {
            $ioncubeInfo = ioncube_file_info();
            if (isset($ioncubeInfo['FILE_EXPIRY'])) {
                $dataExpiracao = date('d/m/Y', $ioncubeInfo['FILE_EXPIRY']);
                $stringExp = '<h6>Sua licença expira em ' . $dataExpiracao . '. Para renovar sua licença, entre em contato com nosso <a href="https://superempreendedor.zendesk.com/" target="_blank">suporte.</a></h6><br/>';
            }
        }
        return sprintf($stringExp);
    }
}