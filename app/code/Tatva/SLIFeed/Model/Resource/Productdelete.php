<?php

namespace Tatva\SLIFeed\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Productdelete extends AbstractDb
{
	/**
	*	Define main table
	*/
	protected function _construct()
	{
		$this->_init('catalog_product_entity_deleted_log','entity_id');
	}
}