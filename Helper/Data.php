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
namespace Marcwatts\Paymate\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getDirectpostUrlResult()
    {
        return $this->_getUrl('paymate/paymate/success', ['_secure'=>true]);
    }

    /**
     * @param $text
     * @return \Magento\Framework\Phrase
     */
    public function wrapGatewayError($text)
    {
        return __('Gateway error: %1', $text);
    }
}
