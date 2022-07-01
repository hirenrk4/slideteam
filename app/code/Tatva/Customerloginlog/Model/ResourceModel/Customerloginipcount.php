<?php
namespace Tatva\Customerloginlog\Model\ResourceModel;

class Customerloginipcount extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_idFieldName = 'customerloginipcount_id';
	
	public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('customerloginipcount', 'customerloginipcount_id');
	}
}