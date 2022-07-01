<?php
namespace Vgroup65\Testimonial\Model\ResourceModel\Testimonial;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'testimonial_id';
	protected $_eventPrefix = 'vgroup_testimonial_collection';
	protected $_eventObject = 'testimonial_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Vgroup65\Testimonial\Model\Testimonial', 'Vgroup65\Testimonial\Model\ResourceModel\Testimonial');
	}

}