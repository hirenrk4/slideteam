<?php
namespace Tatva\Deleteaccount\Model;

class Deletedcustomerbkp extends \Magento\Framework\Model\AbstractModel
{
	const CACHE_TAG = 'customer_id';
	
	protected function _construct()
    {
    	$this->_init('Tatva\Deleteaccount\Model\ResourceModel\Deletedcustomerbkp');
    }
}