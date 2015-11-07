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

define('IDEASA_LOGGER_LEVEL', (isset($_SERVER['MAGE_IS_DEVELOPER_MODE']) ? 'DEBUG' : 'INFO'));
define('IDEASA_LOGGER_FILE', Mage::getBaseDir('var') . DS . 'log' . DS . 'pagseguro-transparente-%s.log');
define('IDEASA_LOGGER_DEFAULT_APPENDER', 'Ideasa_Log4php_Appenders_LoggerAppenderDailyFile');

class Ideasa_Base_Logger extends Ideasa_Log4php_Logger {

    private static $configurationFile;
    
    private static $configured = false;
    
    private static $appenders = array('file' => array('log4php.appender.default.Threshold'                => IDEASA_LOGGER_LEVEL,
                                                      'log4php.appender.default'                          => IDEASA_LOGGER_DEFAULT_APPENDER,
                                                      'log4php.appender.default.file'                     => IDEASA_LOGGER_FILE,
                                                      'log4php.appender.default.datePattern'              => 'Ymd',
                                                      'log4php.appender.default.layout'                   => 'Ideasa_Log4php_Layouts_LoggerLayoutPattern',
                                                      'log4php.appender.default.layout.conversionPattern' => '"%d{Y-m-d H:i:s.u} %c:%L %-5p %m%n"'));

    public static function getLogger($name) {
        if (!self::$configured) {
            self::configureLog4php();
            self::$configured = true;
        }
        return parent::getLogger($name);
    }
    
    private static function configureLog4php() {
        if (self::$configured == false) {
            self::$configured = true;
            self::$configurationFile = dirname(__FILE__) . DS . 'log4php-ideasa.properties';
            
            $configutations = self::fillAllAppenders();
            self::generateConfigFile($configutations);
            Ideasa_Log4php_Logger::configure(self::$configurationFile);
        }
    }
    
    private static function generateConfigFile($content) {
        if (!file_exists(self::$configurationFile)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
                chmod(self::$configurationFile, 0777);
            }
            $fh = fopen(self::$configurationFile, 'a', false) or die("Impossível abrir arquivo " . self::$configurationFile);
            foreach ($content as $key => $value) {
                fwrite($fh, $key . '=' . $value . "\n");
            }
            fclose($fh);
        }
    }

    private static function fillAllAppenders() {
        $configutations = array('log4php.rootLogger' => IDEASA_LOGGER_LEVEL . ', default');
        foreach (self::$appenders as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $configutations[$key2] = $value2;
            }
        }
        return $configutations;
    }
}