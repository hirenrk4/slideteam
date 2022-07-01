<?php
namespace Tatva\Deleteaccount\Model;

class Subscriptionbkp extends \Magento\Framework\Model\AbstractModel
{
	const CACHE_TAG = 'subscription_bkp_id';
	
	protected function _construct()
    {
    	$this->_init('Tatva\Deleteaccount\Model\ResourceModel\Subscriptionbkp');
    }
}