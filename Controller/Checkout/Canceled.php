<?php

namespace Marcwatts\Paymate\Controller\Checkout;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderManagementInterface;

class Canceled extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    protected $_orderFactory;
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
        OrderFactory $orderFactory
   ) {

   $this->orderFactory = $orderFactory;
   $this->orderMgt = $orderMgt;
   
   parent::__construct($context);
}


    public function execute()
    {
        

      // $this->getRequest()->setPostValue('cas_user_cancelled', 1);
       // $this->getRequest()->setPostValue('cas_reference', '000000040');

       $casCancelled = $this->getRequest()->getParam('cas_user_cancelled');
       $casReference = $this->getRequest()->getParam('cas_reference');

       if ( $casCancelled &&  $casReference && strlen($casReference) > 1 ){
            
            $order = $this->orderFactory->create()->loadByIncrementId($casReference);

            if ($order){
                if (  $order->getStatus() == $order->getConfig()->getStateDefaultStatus(Order::STATE_NEW) ){
                    $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));

                    $this->orderMgt->cancel($order->getId());
                    $order->save();
                    echo "Canceled";
                } else {
                    echo "Not Canceled";
                }
                
            }
            
            


        } else {
            echo "Failed";
       }

       
      
    }
}

?>