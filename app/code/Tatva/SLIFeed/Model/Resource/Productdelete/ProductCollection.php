<?php

namespace Tatva\SLIFeed\Model\Resource\Productdelete;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ProductCollection extends AbstractCollection
{
	/**
	*	Define model & resource model
	*/
	protected function _construct()
	{
		$this->_init('Tatva\SLIFeed\Model\Productdelete','Tatva\SLIFeed\Model\Resource\Productdelete');
	}
}