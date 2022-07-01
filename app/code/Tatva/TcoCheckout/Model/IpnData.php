<?php
namespace Tatva\TcoCheckout\Model;


class IpnData extends \Magento\Framework\Model\AbstractModel
{
	
	protected function _construct()
	{
		$this->_init('Tatva\TcoCheckout\Model\ResourceModel\IpnData');
	}

}