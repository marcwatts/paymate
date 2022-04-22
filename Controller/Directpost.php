<?php
/**
* Marc Watts
* Copyright (c) 2022 Marc Watts <marc@marcwatts.com.au>
*
* @author Marc Watts <marc@marcwatts.com.au>
* @copyright Copyright (c) Marc Watts (https://marcwatts.com.au/)
* @license Proprietary https://marcwatts.com.au/terms-and-conditions.html
* @package Marcwatts_Paymate
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