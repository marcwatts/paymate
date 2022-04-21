<?php
/**
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
