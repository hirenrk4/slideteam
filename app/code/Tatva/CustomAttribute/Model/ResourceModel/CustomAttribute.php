<?php 
namespace Tatva\CustomAttribute\Model\ResourceModel;
class CustomAttribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
 public function _construct(){
 $this->_init("coupon_customer_relation","entity_id");
 }
}
 ?>