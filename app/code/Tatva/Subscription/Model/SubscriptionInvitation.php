<?php 
namespace Tatva\Subscription\Model;

class SubscriptionInvitation extends \Magento\Framework\Model\AbstractModel 
{
	public function _construct(){
		$this->_init("Tatva\Subscription\Model\ResourceModel\SubscriptionInvitation");
	}
}
?>