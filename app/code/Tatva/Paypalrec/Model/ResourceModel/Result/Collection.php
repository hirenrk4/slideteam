<?php
namespace Tatva\Paypalrec\Model\ResourceModel\Result;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
  	protected $_idFieldName = 'id';

	protected function _construct()
	{
		$this->_init('Tatva\Paypalrec\Model\Result', 'Tatva\Paypalrec\Model\ResourceModel\Result');
	}
}