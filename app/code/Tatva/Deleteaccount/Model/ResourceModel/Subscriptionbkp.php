<?php
namespace Tatva\Deleteaccount\Model\ResourceModel;

class Subscriptionbkp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_idFieldName = 'subscription_bkp_id';
	public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('tatva_delacc_subscription_bkp', 'subscription_bkp_id');
	}
}