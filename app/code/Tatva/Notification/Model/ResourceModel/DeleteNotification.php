<?php
namespace Tatva\Notification\Model\ResourceModel;
class DeleteNotification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('tatva_delete_notification', 'delete_id');
	}
	
}