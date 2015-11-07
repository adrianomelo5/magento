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

class Ideasa_Base_Model_System_Config_Source_Horas {

    public function toOptionArray() {
        $opcoes = array();
        for ($i = 0; $i <= 23; $i++) {
            $hora = str_pad($i, 2, '0', STR_PAD_LEFT);
            $opcoes[$hora] = $hora;
        }
        return $opcoes;
    }

}