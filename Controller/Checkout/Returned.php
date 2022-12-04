<?php

namespace Marcwatts\Paymate\Controller\Checkout;

use Magento\Sales\Model\Order;

class Returned extends \Marcwatts\Paymate\Controller\AbstractCheckoutAction
{
    /**
     * Redirect to checkout
     *
     * @return void
     */
    protected $messageManager;
    protected $urlBuilder;


    public function execute()
    {
        
        $order = $this->getOrder();
 
        if (! isset($order)) {
            return;
        }


        $this->messageManager = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Magento\Framework\Message\ManagerInterface');
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Magento\Framework\UrlInterface');


     
        // cancelled orders
        if ( $order->getStatus() == $order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED)){
            $this->messageManager->addNotice(__("Your order has been canceled."));
            $this->_redirect($this->urlBuilder->getUrl('checkout/cart',  ['_secure' => true]));
        }

        // successful orders
        if ( $order->getStatus() == $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING)){
            $this->_redirect($this->urlBuilder->getUrl('checkout/onepage/success/',  ['_secure' => true]));
        }
       
        
        // we read the GET param as a fall back in case order hasn been updated
        if (isset($_REQUEST) && isset($_REQUEST['cas_cancelled_code'])){
            $this->_redirect($this->urlBuilder->getUrl('checkout/cart',  ['_secure' => true]));
        }
        //if ( $order->getStatus() == $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING)){
           // echo $order->getIncrementId();
       if ( $order->getStatus() == 'pending'){
            $orderTotal = number_format((float) $order->getGrandTotal(), 2, '.', '');

            $orderMgt =  $this->_objectManager->get('Magento\Sales\Api\OrderManagementInterface');
            $merchantId =  $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/merchant_id');
            $rusername = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/reportusername');
            $rpassword = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/reportpassword');
            $encryptor = $this->_objectManager->get('Magento\Framework\Encryption\EncryptorInterface');

            $rpassword = $encryptor->decrypt($rpassword);

            $url = "https://api.cardaccess.com.au/live/etx/reports/v3/reports/transaction/?start_time=".date('Y-m-d')."T00:00:00&end_time=".date('Y-m-d')."T23:59:00&CAS.CUSTREF=".$order->getIncrementId()."&mer_id=".$merchantId."&amount=" . $orderTotal;

 
               
            $credentials = base64_encode("$rusername:$rpassword");
            
            $headers = [];
            $headers[] = "Authorization: Basic $credentials";
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Cache-Control: no-cache';
            
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            } else{
                curl_close($ch);
                // Debug the result

                
                $result = json_decode($result); 
                
                if (sizeof($result->results) > 0){
                   if (isset($result->results[0]->txn_status)){
                        $transactionStatus = $result->results[0]->txn_status;

                        if (  $transactionStatus == 'DECLINED'){
                            $this->messageManager->addNotice(__("Your order has been declined."));
                            $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                           ;
                            $orderMgt->cancel($order->getId());
                            $order->save();
                        }

                        if (  $transactionStatus == 'APPROVED'){
                            
                            $order->setState(Order::STATE_PROCESSING)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                            $this->_objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
       

                            $payment = $order->getPayment();
                            $transactionRepository = $this->_objectManager->create('\Magento\Sales\Api\TransactionRepositoryInterface');
                            $invoiceService = $this->_objectManager->create('\Magento\Sales\Model\Service\InvoiceService');
                            $invoiceSender = $this->_objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                            
                            $transaction = $transactionRepository->getByTransactionId(
                                    "-1",
                                    $payment->getId(),
                                    $order->getId()
                            );


                            if ($order->canInvoice()) {
                                $invoice = $invoiceService->prepareInvoice($order);
                                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                                $invoice->register();
                                $invoice->save();
                                $transactionSave = $transaction->addObject(
                                    $invoice
                                )->addObject(
                                    $invoice->getOrder()
                                );
                                
                                $transactionSave->save();

                                try {
                                    $invoiceSender->send($invoice);
                                    } catch (\Exception $e) {
                                    $this->messageManager->addError(__('We can\'t send the invoice email right now.'));
                                    }
                            }

                         
                            $payment->addTransactionCommentsToOrder(
                                $transaction,
                                "Transaction of value " . $result->results[0]->amount . " completed successfully"
                            );
                            $payment->setParentTransactionId(null); 
                           // $payment->setTransactionId($_REQUEST['cas_reference']);
                            $payment->setIsTransactionClosed(0);
                            
                            $order->setCanSendNewEmailFlag(false);
                            
                            $payment->save();
                            $order->save();

                            $this->_redirect($this->urlBuilder->getUrl('checkout/onepage/success/',  ['_secure' => true]));

                        }
                   }
                }
                
            }
            
            
            
        }
        
        //
        $this->_redirect($this->urlBuilder->getUrl('checkout/cart',  ['_secure' => true]));
    }
}

?>