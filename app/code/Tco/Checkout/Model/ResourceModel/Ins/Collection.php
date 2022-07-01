<?php

namespace Tco\Checkout\Model\ResourceModel\Ins;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{

	protected $_idFieldName = 'id';

	protected function _construct()
	{
		$this->_init('Tco\Checkout\Model\Ins', 'Tco\Checkout\Model\ResourceModel\Ins');
	}

}
