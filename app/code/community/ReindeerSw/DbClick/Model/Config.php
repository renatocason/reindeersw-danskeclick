<?php
/**
 * @copyright Copyright (c) 2014 Reindeer Software (http://reindeersw.com)
 */
class ReindeerSw_DbClick_Model_Config {
    
    /**
     * 
     * @return boolean
     */
    public function getEnabled() {
        return Mage::getStoreConfig('payment/reindeersw_dbclick/active');
    }
    
    /**
     * 
     * @return string
     */
    public function getSubmitUrl() {
        return Mage::getStoreConfig('payment/reindeersw_dbclick/submit_url');
    }
    
    /**
     * 
     * @return integer
     */
    public function getVkSndID() {
        return Mage::getStoreConfig('payment/reindeersw_dbclick/vk_snd_id');
    }
    
    /**
     * 
     * @return string
     */
    public function getClientPrivateKey() {
        return Mage::getStoreConfig('payment/reindeersw_dbclick/client_private_key');
    }
    
    /**
     * 
     * @return string
     */
    public function getClientKeyPassphrase() {
        return Mage::getStoreConfig('payment/reindeersw_dbclick/client_key_passphrase');
    }
    
    /**
     * 
     * @return string
     */
    public function getBankPublicKey() {
        return Mage::getStoreConfig('payment/reindeersw_dbclick/bank_public_key');
    }
}