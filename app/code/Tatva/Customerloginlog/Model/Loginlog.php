<?php
namespace Tatva\Customerloginlog\Model;

class Loginlog extends \Magento\Framework\Model\AbstractModel
{
	const CACHE_TAG = 'customerloginlog';
	
	protected function _construct()
    {
    	$this->_init('Tatva\Customerloginlog\Model\ResourceModel\Loginlog');
    }
}