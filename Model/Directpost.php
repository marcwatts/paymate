<?php

namespace Marcwatts\Paymate\Model;

class Directpost extends \Magento\Payment\Model\Method\AbstractMethod
{
 
    const METHOD_CODE = 'paymate';
	protected $_isInitializeNeeded = false;
    protected $redirect_uri;
    protected $_code  = self::METHOD_CODE;
 	protected $_canOrder = true;
    protected $_canCapture = true;
	protected $_isGateway = true; 
    protected $_canRefund = false;
	

}
