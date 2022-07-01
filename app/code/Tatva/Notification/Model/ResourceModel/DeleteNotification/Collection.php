<?php
namespace Tatva\Notification\Model\ResourceModel\DeleteNotification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'delete_id';
	protected $_eventPrefix = 'tatva_delete_notification';
	protected $_eventObject = 'deletenotification_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Tatva\Notification\Model\DeleteNotification', 'Tatva\Notification\Model\ResourceModel\DeleteNotification');
	}

}
