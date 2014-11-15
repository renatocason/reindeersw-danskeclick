<?php
/**
 * @copyright Copyright (c) 2014 Reindeer Software (http://reindeersw.com)
 */
class ReindeerSw_DbClick_StandardController extends Mage_Core_Controller_Front_Action {
    
    /**
     * Redirect form creation
     */
    public function redirectAction() {
        iconv_set_encoding("internal_encoding", "UTF-8");
        iconv_set_encoding("output_encoding", "Windows-1257");
        
        $this -> getResponse()
                -> setHeader("Content-Type", "text/html; charset=Windows-1257", true)
                -> setBody($this -> getLayout() -> createBlock('reindeersw_dbclick/standard_redirect') -> toHtml());
    }
    
    /**
     * User redirect - payment canceled
     */
    public function cancelAction()
    {
        $this -> _redirect('checkout/cart');
    }

    /**
     * User redirect - successful payment
     */
    public function  successAction()
    {
        Mage::getSingleton('checkout/session') -> getQuote() -> setIsActive(false) -> save();
        $this -> _redirect('checkout/onepage/success', array('_secure'=>true));
    }
    
    /**
     * Server to server call
     */
    public function returnAction() {
        $params = $this -> getRequest() -> getParams();
        $mac_helper = Mage::helper('reindeersw_dbclick/mac');
        
        // Response logging
        Mage::log($params, null, 'reindeersw_dbclick.log', true);
        
        // Mac check
        $mac = $mac_helper -> Compose($params, 'Windows-1251');
        
        if(!$mac_helper -> Verify($mac, $params['VK_MAC'])) {
            Mage::log('Wrong MAC parameter', null, 'reindeersw_dbclick.log', true);
            return;
        }
        
        // Order canceled
        $order = Mage::getModel('sales/order')
            -> loadByIncrementId($params['VK_REF']);
        
        // Payment delayed
        if($params['VK_SERVICE'] == '1201') {
            Mage::log("Payment delayed for order #" . $order -> getIncrementId(), null, 'reindeersw_dbclick.log', true);
            $this -> _redirect('checkout/cart');
            return;
        }
        
        if($params['VK_SERVICE'] != '1101') {
            // Cancel order if necessary
            if($order -> getStatus() != "canceled")
                $order -> cancel() -> save();
            
            Mage::log("Payment canceled #" . $order -> getIncrementId(), null, 'reindeersw_dbclick.log', true);
            $this -> cancelAction();
            return;
        }
        
        // Order already confirmed
        if($order -> getStatus() != "pending_payment") {
            $this -> successAction();
            return;
        }
        
        // Saving payment details
        $payment = $order -> getPayment();
        $escapedParams = array();
        foreach($params as $key => $value)
            $escapedParams[$key] = mb_convert_encoding($value, 'UTF-8', 'Windows-1257');
        $payment -> setAdditionalData(serialize($escapedParams));
        $payment -> save();
        
        // Invoicing
        $invoice = Mage::getModel('sales/service_order', $order)
            -> prepareInvoice();

        $invoice -> setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice -> register();
        $invoice -> getOrder() -> setCustomerNoteNotify(false);
        $invoice -> getOrder() -> setIsInProcess(true);

        // Save in transaction
        $order -> addStatusHistoryComment('Invoice generation after DanskeBank Click payment', false);
        $transactionSave = Mage::getModel('core/resource_transaction')
            -> addObject($invoice)
            -> addObject($invoice -> getOrder());
        $transactionSave -> save();
    
        // Send notification
        $order -> sendNewOrderEmail() -> addStatusHistoryComment(
                    $this -> __('Notified customer about invoice #%s.', $invoice->getIncrementId())
                )
                -> setIsCustomerNotified(true)
                -> save();
        
        $order  -> setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                -> setStatus('processing')
                -> save();
        
        $this -> successAction();
    }
}