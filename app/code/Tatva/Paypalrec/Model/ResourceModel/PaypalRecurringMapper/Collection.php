<?php
namespace Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'map_id';
	protected $_eventPrefix = 'tatva_paypalrec_pp_recurring_mapper_collection';
	protected $_eventObject = 'pp_recurring_mapper_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Tatva\Paypalrec\Model\PaypalRecurringMapper', 'Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper');
	}

}
