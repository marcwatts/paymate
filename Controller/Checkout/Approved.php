<?php

namespace Marcwatts\Paymate\Controller\Checkout;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Sales\Model\Order;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;


class Approved extends  \Magento\Framework\App\Action\Action  implements CsrfAwareActionInterface
{
   
    protected $_orderFactory;
    protected $transactionRepository;
    protected $invoiceService;

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function __construct( 
             Context $context,
			OrderFactory $orderFactory,
            InvoiceService $invoiceService,
            InvoiceSender $invoiceSender,
            Transaction $transaction,
            \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
		) {

        $this->orderFactory = $orderFactory;
        $this->transactionRepository = $transactionRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
		
		parent::__construct($context);
    }


    public function execute()
    {


        $loggingActive = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/paymate/logging_active');
       
       if (!isset($_REQUEST)){
        echo "Incorrect Response 1";
       }


        if (isset($_REQUEST)){
            if ($loggingActive){
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/paymate.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info("Cancelled Request : " . print_r($this->getRequest(), true));
            }
        }


       if (isset($_REQUEST) && isset($_REQUEST['cas_reference']) && isset( $_REQUEST['cas_amt'])){

                if (isset( $_REQUEST['cas_reference']) && isset( $_REQUEST['audit'])){
                    $auditId = $_REQUEST['audit'];
                    $casOrderId =  $_REQUEST['cas_reference'];
                    $order = $this->orderFactory->create()->loadByIncrementId($casOrderId);


                    $order->setState(Order::STATE_PROCESSING)
							->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
                   

                    $payment = $order->getPayment();

                    $transaction = $this->transactionRepository->getByTransactionId(
                            "-1",
                            $payment->getId(),
                            $order->getId()
                    );


                    if ($order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $invoice->save();
                        $transactionSave = $this->transaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        
                        $transactionSave->save();

                        try {
                            $this->invoiceSender->send($invoice);
                            } catch (\Exception $e) {
                            $this->messageManager->addError(__('We can\'t send the invoice email right now.'));
                            }
                    }

     
                    $payment->addTransactionCommentsToOrder(
                        $transaction,
                         "Transaction of value " . $_REQUEST['cas_amt'] . " completed successfully with audit id " .  $auditId
                    );
                    $payment->setParentTransactionId(null); 
                    $payment->setTransactionId($_REQUEST['cas_reference']);
                    $payment->setIsTransactionClosed(0);
                    
                    $order->setCanSendNewEmailFlag(false);
                    
                    $payment->save();
                    $order->save();

                  

                }
                mail('bjornishere@gmail.com','Paymate approved', print_r($_REQUEST, true));

                echo "CASConnect Notification Accepted";

       } else{
            echo "Incorrect Response";
       }
      
       //die();
    }
}

?>