<?php

namespace Tatva\Metadescription\Model\ResourceModel\Metadescription;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'metadescription_id';

	protected function _construct()
	{
		$this->_init('Tatva\Metadescription\Model\Metadescription', 'Tatva\Metadescription\Model\ResourceModel\Metadescription');
	}

}
