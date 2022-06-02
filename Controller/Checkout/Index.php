<?php

namespace Marcwatts\Paymate\Controller\Checkout;

class Index extends  \Marcwatts\Paymate\Controller\AbstractCheckoutAction
{
    /**
     * Redirect to checkout
     *
     * @return void
     */

    public function execute()
    {
        $active = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/active');
        $merchantId = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/merchant_id');
        $password = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/password');
        $encryptor = $this->_objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
        $urlBuilder = $this->_objectManager->get('Magento\Framework\UrlInterface');

        $debugemail = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/debugemail');
        $loggingActive = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/logging_active');
        $testMode = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/testmode');
        $defaultEmail = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('trans_email/ident_support/email');

        $storeName = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getName();
        
        $debugemail = ( isset($debugemail) && strlen($debugemail)>1) ? $debugemail : $defaultEmail ;

        if (!$active){
            return;
        }

        $order = $this->getOrder();
 
        if (! isset($order)) {
            return;
        }

      
        


       $url = "https://secure.cardaccess.com.au/ecom/casconnect_conv/store_form_update/store_A.py";
       $url2 = "https://secure.cardaccess.com.au/ecom/casconnect_conv/store_form_update/index_A.py?stored.txnkey=";
       $ch = curl_init();

       curl_setopt($ch, CURLOPT_URL,$url);
       curl_setopt($ch, CURLOPT_POST, 1);

       $requestData = array(
        'cas.merid' => $merchantId,
        'cas.merchant_name' => $storeName,
        'cas.approved_email' => $debugemail,
        'CAS.BUYER.FIRSTNAME' => $order->getCustomerFirstname(),
        'CAS.BUYER.LASTNAME' => $order->getCustomerLastname(),
        'CAS.BUYER.EMAIL' => $order->getCustomerEmail(),
        'CAS.BUYER.PHONE' => $order->getBillingAddress()->getTelephone(),
        'CAS.BUYER.COUNTRY' => $order->getBillingAddress()->getCountryId(),
        'CAS.BUYER.ADDRESS1' => implode(' ', $order->getBillingAddress()->getStreet()),
        'CAS.BUYER.ADDRESS2' => ' ',
        'CAS.BUYER.CITYSUB' => $order->getBillingAddress()->getCity(),
        'CAS.BUYER.STATEPROV' => $order->getBillingAddress()->getRegion(),
        'CAS.BUYER.POSTCODE' => $order->getBillingAddress()->getPostCode(),
        'cas.amt' => number_format((float) $order->getGrandTotal(), 2, '.', ''),
        'cas.return_link' => $urlBuilder->getUrl('paymate/checkout/returned',  ['_secure' => true]),
        'cas.cancel_url' => $urlBuilder->getUrl('paymate/checkout/canceled',  ['_secure' => true]),
        'cas.approved_url' => $urlBuilder->getUrl('paymate/checkout/approved',  ['_secure' => true]),
        'cas.declined_url' => $urlBuilder->getUrl('paymate/checkout/declined',  ['_secure' => true]),
        'cas.reference' => $order->getIncrementId(),
        'cas.merchant_password' => $encryptor->decrypt($password),
        'cas.istest' => 0
       );

      // if($testMode){
       //     $requestData['cas.istest'] = 1;
      // }
        curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query(
           $requestData )
        );
       
        if ($loggingActive){
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/paymate.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info("Request : " . print_r($requestData,true));
        }
        


       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       
       $server_output = curl_exec($ch);

       if ($loggingActive){
        $logger->info("Return : " . print_r($server_output,true));
    }

       $params = explode("\n", $server_output);
       curl_close ($ch);

       if ($params[0] == 'result=OK'){
            $txncode = explode('stored.txnkey=', $server_output); 
            $redirectUrl = $url2 . $txncode[1];
       } else {
            $redirectUrl = null; 
       }

      
         
        if (isset($redirectUrl)) {
                $this->getResponse()->setRedirect($redirectUrl);
            } else {
                $this->redirectToCheckoutFragmentPayment();
            }
        }
    
}
