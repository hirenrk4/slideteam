<?php
namespace Vgroup65\Testimonial\Model\ResourceModel\Configuration;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'configuration_id';
	protected $_eventPrefix = 'vgroup_configuration_collection';
	protected $_eventObject = 'configuration_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Vgroup65\Testimonial\Model\Configuration', 'Vgroup65\Testimonial\Model\ResourceModel\Configuration');
	}

}