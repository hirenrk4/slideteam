<?php

namespace Tatva\ProductImport\Model\ResourceModel\ProductImport;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'profiler_id';

	protected function _construct()
	{
		$this->_init('Tatva\ProductImport\Model\ProductImport', 'Tatva\ProductImport\Model\ResourceModel\ProductImport');
	}

}
