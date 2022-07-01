<?php

namespace Tatva\PaidCustomerPopup\Model\ResourceModel;

class CustomerAdditionlData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	protected $_idFieldName = 'id';
	protected $_date;

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	){
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('paid_customer_additional_data', 'id');
	}

}
