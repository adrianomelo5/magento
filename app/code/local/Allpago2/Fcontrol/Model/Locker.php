<?php

/**
 * Allpago Module for Fcontrol
 *
 * @title      Magento -> Custom Module for Fcontrol
 * @category   Fraud Control Gateway
 * @package    Allpago_Fcontrol
 * @author     Allpago Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2013 Allpago
 */
class Allpago_Fcontrol_Model_Locker extends Mage_Core_Model_Abstract {
    //Constantes utilizadas
    const ROBOS_ID = 'allpago_robo_';

    //Variáveis globais utilizadas
    public $_id = null;
    protected $_isLocked = null;
    protected $_lockFile = null;

    /**
     * Minimun of a 2 char length ID
     *
     * @return bool
     */
    public function validateObject() {
        return ($this->_id != null && strlen($this->_id) > 2) ? true : false;
    }

    /**
     * Get lock file resource
     *
     * @return resource
     */
    protected function _getLockFile() {
        if (!$this->validateObject())
            return false;
        if ($this->_lockFile === null) {
            $varDir = Mage::getConfig()->getVarDir('locks');
            $file = $varDir . DS . self::ROBOS_ID . $this->_id . '.lock';
            $this->_lockFile = fopen($file, 'w');
            fwrite($this->_lockFile, date('r'));
        }
        return $this->_lockFile;
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process runing and fast lock validation.
     *
     * @return Mage_Index_Model_Process
     */
    public function lock() {
        if (!$this->validateObject())
            return false;
        $this->_isLocked = true;
        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);
        return $this;
    }

    /**
     * Lock and block process.
     * If new instance of the process will try validate locking state script will wait until process will be unlocked
     *
     * @return Mage_Index_Model_Process
     */
    public function lockAndBlock() {
        if (!$this->validateObject())
            return false;
        $this->_isLocked = true;
        flock($this->_getLockFile(), LOCK_EX);
        return $this;
    }

    /**
     * Unlock process
     *
     * @return Mage_Index_Model_Process
     */
    public function unlock() {
        if (!$this->validateObject())
            return false;
        $this->_isLocked = false;
        flock($this->_getLockFile(), LOCK_UN);
        return $this;
    }

    /**
     * Check if process is locked
     *
     * @return bool
     */
    public function isLocked() {
        if (!$this->validateObject())
            return false;
        if ($this->_isLocked !== null)
            return $this->_isLocked;
        else {
            $fp = $this->_getLockFile();
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                flock($fp, LOCK_UN);
                return false;
            }
            return true;
        }
    }

    /**
     * Close file resource if it was opened
     */
    public function __destruct() {
        $this->unlock();
        if ($this->_lockFile)
            fclose($this->_lockFile);
    }

}