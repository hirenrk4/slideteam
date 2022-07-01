<?php

namespace Tatva\Customer\Model\ResourceModel;

class Killedsesssions extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
		 $resourcePrefix = null
		)
	{
		parent::__construct($context, $resourcePrefix);
	}

	protected function _construct()
	{
		$this->_init('tatva_customer_killed_sessions', 'id');
	}

}
