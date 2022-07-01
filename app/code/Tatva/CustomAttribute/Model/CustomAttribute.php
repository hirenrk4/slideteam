<?php 
namespace Tatva\CustomAttribute\Model;
class CustomAttribute extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Tatva\CustomAttribute\Model\ResourceModel\CustomAttribute");
	}
}
?>