<?php
namespace Tatva\Deleteaccount\Model\ResourceModel;

class Deletedcustomerbkp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_idFieldName = 'customer_id';
	public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('tatva_delacc_customer_bkp', 'customer_id');
		$this->_isPkAutoIncrement = false;
	}
}