<?php
/**

 */
namespace Marcwatts\Paymate\Model\Config\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{

    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'OT'];
    }
}
