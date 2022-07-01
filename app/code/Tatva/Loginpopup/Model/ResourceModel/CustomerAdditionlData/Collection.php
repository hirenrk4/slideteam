<?php

namespace Tatva\Loginpopup\Model\ResourceModel\CustomerAdditionlData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'id';

	protected function _construct()
	{
		$this->_init('Tatva\Loginpopup\Model\CustomerAdditionlData', 'Tatva\Loginpopup\Model\ResourceModel\CustomerAdditionlData');
	}

}
