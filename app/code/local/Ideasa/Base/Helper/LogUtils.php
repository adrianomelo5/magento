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

class Ideasa_Base_Helper_LogUtils {

    public static function varDump($variable) {
        $logFile = Mage::getBaseDir('var') . DS . 'log' . DS . Mage::getStoreConfig('dev/log/file');
        ob_start();
        // write content
        if (is_object($variable)) {
            //new Ideasa_Print($variable);
              var_dump($variable);
        } else {
            var_dump($variable);
        }
        $content = ob_get_contents();
        ob_end_clean();
        file_put_contents($logFile, $content, FILE_APPEND);
    }

}

class Ideasa_Print {

    public function __construct($class) {
        $api = new ReflectionClass($class);

        foreach ($api->getProperties() as $propertie) {
            print $propertie->getName() . "\n";
            print $propertie->getValue($class);
        }
        
        
    }
    
    

}