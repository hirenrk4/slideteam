<?php 
namespace Tatva\Subscription\Model\ResourceModel\SubscriptionInvitation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Tatva\Subscription\Model\SubscriptionInvitation","Tatva\Subscription\Model\ResourceModel\SubscriptionInvitation")
		;
	}
}
 ?>