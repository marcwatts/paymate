<?php
/**
 */
namespace Marcwatts\Paymate\Controller;

abstract class AbstractCheckoutAction extends \Magento\Framework\App\Action\Action
{
    protected $nabHelper;

    protected $checkoutSession;

    protected $storeManager;

    protected $orderFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Marcwatts\Paymate\Helper\Data $paymateHelper
    ) {
        parent::__construct($context);
        $this->paymate = $paymateHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
    }
}