<?php
namespace Tatva\Notification\Model\ResourceModel\Notification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'notification_id';
	protected $_eventPrefix = 'tatva_notification';
	protected $_eventObject = 'notification_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Tatva\Notification\Model\Notification', 'Tatva\Notification\Model\ResourceModel\Notification');
	}

}
