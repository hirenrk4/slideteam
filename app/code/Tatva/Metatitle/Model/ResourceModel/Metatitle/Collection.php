<?php

namespace Tatva\Metatitle\Model\ResourceModel\Metatitle;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'metatitle_id';

	protected function _construct()
	{
		$this->_init('Tatva\Metatitle\Model\Metatitle', 'Tatva\Metatitle\Model\ResourceModel\Metatitle');
	}

}
