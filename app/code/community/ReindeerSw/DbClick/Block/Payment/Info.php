<?php
/**
 * @copyright Copyright (c) 2014 Reindeer Software (http://reindeersw.com)
 */
class ReindeerSw_DbClick_Block_Payment_Info extends Mage_Payment_Block_Info_Cc {
    
    public function getCcTypeName() {
        return 'DanskeBank Click';
    }
    
    /**
     * Prepare specific payment information
     *
     * @param Varien_Object|array $transport
     * return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null) {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = $this -> getInfo() -> getAdditionalData();
        
        if(empty($data))
            return $transport;
        
        $obj_data = unserialize($data);
        return $transport -> addData(array(
            $this -> __('Payment Number') => $obj_data['VK_T_NO'],
            $this -> __('Payer\'s Account') => $obj_data['VK_SND_ACC'],
            $this -> __('Payer\'s Name') => $obj_data['VK_SND_NAME'],
        ));
    }
    
}