<?php 
namespace Tatva\Subscription\Model\ResourceModel;
class SubscriptionInvitation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

	public function _construct(){
 		$this->_init("subscription_invitation","invitation_id");
 	}
}
?>