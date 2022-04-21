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

    }
}

?>