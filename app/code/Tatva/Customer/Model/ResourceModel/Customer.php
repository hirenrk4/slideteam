<?php

namespace Tatva\Customer\Model\ResourceModel;

class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	protected $_idFieldName = 'entity_id';

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		 $resourcePrefix = null
		)
	{
		parent::__construct($context, $resourcePrefix);
	}

	protected function _construct()
	{
		$this->_init('customer_entity', 'entity_id');
	}

}
