<?php 
namespace Tatva\Popup\Model;

class Popup extends \Magento\Framework\Model\AbstractModel{
	public function _construct(){
		$this->_init("Tatva\Popup\Model\ResourceModel\Popup");
	}
}
 ?>