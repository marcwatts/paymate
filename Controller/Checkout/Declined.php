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
use Magento\Sales\Api\OrderManagementInterface;

class Declined extends \Magento\Framework\App\Action\Action  implements CsrfAwareActionInterface
{

    protected $_orderMgt;

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
        OrderManagementInterface $orderMgt,
       OrderFactory $orderFactory,
       InvoiceService $invoiceService,
       Transaction $transaction,
       \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
   ) {
    $this->orderMgt = $orderMgt;
   $this->orderFactory = $orderFactory;
   $this->transactionRepository = $transactionRepository;
   $this->invoiceService = $invoiceService;
   $this->transaction = $transaction;
   
   parent::__construct($context);
}


public function execute()
{



  if (!isset($_REQUEST)){
        echo "Incorrect Response 1";
        return;
  }


  if (isset($_REQUEST) && isset($_REQUEST['cas_reference']) && isset( $_REQUEST['cas_amt'])){

           if (isset( $_REQUEST['cas_reference']) && isset( $_REQUEST['audit'])){
               $auditId = $_REQUEST['audit'];
               $casOrderId =  $_REQUEST['cas_reference'];
               if (isset($_REQUEST['result']) && $_REQUEST['result'] == 'DECLINED'){

                        $order = $this->orderFactory->create()->loadByIncrementId($casOrderId);


                        if (  $order->getStatus() == $order->getConfig()->getStateDefaultStatus(Order::STATE_NEW) ){
                            $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
        
                            $this->orderMgt->cancel($order->getId());
                            $order->save();
                        } 

                        
                        $payment = $order->getPayment();

                        $transaction = $this->transactionRepository->getByTransactionId(
                            "-1",
                            $payment->getId(),
                            $order->getId()
                    );

                        $payment->addTransactionCommentsToOrder(
                            $transaction,
                                "Transaction of value " . $_REQUEST['cas_amt'] . " was declined with audit number " .  $auditId
                        );
                        $payment->setParentTransactionId(null); 

                        

                        $order->save();
               }
               
 

           }
          

           echo "CASConnect Notification Accepted";

  } else{
       echo "Incorrect Response";
  }
 
  //die();
}
}



?>