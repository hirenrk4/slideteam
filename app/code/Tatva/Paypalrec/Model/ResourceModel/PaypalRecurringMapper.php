<?php
namespace Tatva\Paypalrec\Model\ResourceModel;


class PaypalRecurringMapper extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('tatva_pp_recurring_mapper', 'map_id');
	}
	
}