<?php

namespace Tatva\SLIFeed\Model;

use Magento\Framework\Model\AbstractModel;

class Productdelete extends AbstractModel
{
	/**
	*	Define resource model
	*/
	protected function _construct()
	{
		$this->_init('Tatva\SLIFeed\Model\Resource\Productdelete');
	}
}