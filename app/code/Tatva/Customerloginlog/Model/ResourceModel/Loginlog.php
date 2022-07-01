<?php
namespace Tatva\Customerloginlog\Model\ResourceModel;

class Loginlog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_idFieldName = 'customerloginlog_id';
	public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('customerloginlog', 'customerloginlog_id');
	}
}