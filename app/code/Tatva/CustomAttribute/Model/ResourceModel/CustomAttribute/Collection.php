<?php 
namespace Tatva\CustomAttribute\Model\ResourceModel\CustomAttribute;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Tatva\CustomAttribute\Model\CustomAttribute","Tatva\CustomAttribute\Model\ResourceModel\CustomAttribute");
	}
}
 ?>