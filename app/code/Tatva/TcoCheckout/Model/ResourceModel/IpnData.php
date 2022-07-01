<?php
namespace Tatva\TcoCheckout\Model\ResourceModel;


class IpnData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	protected function _construct()
	{
		$this->_init('2checkout_ipn_cron', 'id');
	}
	
}