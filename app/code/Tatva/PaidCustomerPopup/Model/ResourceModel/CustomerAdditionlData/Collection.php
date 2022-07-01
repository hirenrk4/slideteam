<?php

namespace Tatva\PaidCustomerPopup\Model\ResourceModel\CustomerAdditionlData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'id';
	
	protected function _construct()
	{
		$this->_init('Tatva\PaidCustomerPopup\Model\CustomerAdditionlData', 'Tatva\PaidCustomerPopup\Model\ResourceModel\CustomerAdditionlData');
	}

}
