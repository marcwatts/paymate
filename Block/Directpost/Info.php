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
namespace Marcwatts\Paymate\Block\Directpost;

use Magento\Framework\View\Element\Template;

class Info extends \Magento\Payment\Block\Info
{
    protected $_template = 'Marcwatts_Paymate::directpost/info.phtml';


    protected $paymateHelper;


    public function __construct(
        \Marcwatts\Paymate\Helper\Data $paymateHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymateHelper = $paymateHelper;
    }
}
