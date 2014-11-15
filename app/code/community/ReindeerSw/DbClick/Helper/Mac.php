<?php
/**
 * @copyright Copyright (c) 2014 Reindeer Software (http://reindeersw.com)
 */
class ReindeerSw_DbClick_Helper_Mac extends Mage_Core_Helper_Data {
    static $FieldNames = array(
            '1001' => array('VK_SERVICE', 'VK_VERSION', 'VK_SND_ID', 'VK_STAMP', 'VK_AMOUNT', 'VK_CURR', 'VK_TERM'/*, 'VK_ACC'*/, 'VK_PCODE'/*, 'VK_PANK', 'VK_NAME'*/, 'VK_REF', 'VK_MSG'),
            '1101' => array('VK_SERVICE', 'VK_VERSION', 'VK_SND_ID', 'VK_REC_ID', 'VK_STAMP', 'VK_AMOUNT', 'VK_CURR', 'VK_REC_ACC', 'VK_REC_NAME', 'VK_SND_ACC', 'VK_SND_NAME', 'VK_REF', 'VK_MSG', 'VK_T_DATE'),
            '1201' => array('VK_SERVICE', 'VK_VERSION', 'VK_SND_ID', 'VK_REC_ID', 'VK_STAMP', 'VK_AMOUNT', 'VK_CURR', 'VK_REC_ACC', 'VK_REC_NAME', 'VK_SND_ACC', 'VK_SND_NAME', 'VK_REF', 'VK_MSG'),
            '1901' => array('VK_SERVICE', 'VK_VERSION', 'VK_SND_ID', 'VK_REC_ID', 'VK_STAMP', 'VK_REF', 'VK_MSG'),
        );
    
    public function Compose($fields, $encoding='UTF-8') {
        $mac = "";
        
        foreach(self::$FieldNames[$fields['VK_SERVICE']] as $name)
            $mac .= str_pad(mb_strlen($fields[$name], $encoding), 3, '0', STR_PAD_LEFT) . $fields[$name];
        
        return $mac;
    }
    
    public function Sign($data) {
        // Load configuration model
        $config = Mage::getModel('reindeersw_dbclick/config');
        
        // Open the private key
        $pkey_id = openssl_get_privatekey($config -> getClientPrivateKey(), $config -> getClientKeyPassphrase());
        
        // Sing data
        $signature = "";
        openssl_sign($data, $signature, $pkey_id);
        
        // Encode signature
        $signature = base64_encode($signature);
        
        // Release key's resource
        openssl_free_key($pkey_id);
        
        return $signature;
    }
    
    public function Verify($data, $signature) {
        // Load configuration model
        $config = Mage::getModel('reindeersw_dbclick/config');
        
        // Open the private key
        $pkey_id = openssl_get_publickey($config -> getBankPublicKey());
        
        // Verify
        $result = openssl_verify($data, base64_decode($signature), $pkey_id);
        
        // Release key's resource
        openssl_free_key($pkey_id);
        
        return $result;
    }
    
}