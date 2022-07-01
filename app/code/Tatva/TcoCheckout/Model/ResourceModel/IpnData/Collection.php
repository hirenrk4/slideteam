<?php
namespace Tatva\TcoCheckout\Model\ResourceModel\IpnData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Tatva\TcoCheckout\Model\IpnData', 'Tatva\TcoCheckout\Model\ResourceModel\IpnData');
	}

}